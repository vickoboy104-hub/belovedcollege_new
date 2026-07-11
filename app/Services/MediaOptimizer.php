<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;
use Throwable;

class MediaOptimizer
{
    public function optimizeUploadedFile(UploadedFile $file): array
    {
        if (! $file->isValid() || ! $file->getRealPath()) {
            return $this->result('skipped', 0, 0, 'Invalid upload.');
        }

        return $this->optimizePath($file->getRealPath(), $file->getClientOriginalExtension());
    }

    public function optimizePath(string $path, ?string $extension = null): array
    {
        if (! is_file($path) || ! is_readable($path) || ! is_writable($path)) {
            return $this->result('skipped', 0, 0, 'File is not readable and writable.');
        }

        $before = (int) filesize($path);
        $extension = strtolower($extension ?: pathinfo($path, PATHINFO_EXTENSION));
        $mime = $this->detectMime($path);

        try {
            if (str_starts_with($mime, 'image/')) {
                return $this->optimizeImage($path, $mime, $extension, $before);
            }

            if (str_starts_with($mime, 'video/')) {
                return $this->optimizeVideo($path, $extension, $before);
            }
        } catch (Throwable $exception) {
            Log::warning('Media optimization failed.', [
                'path' => $path,
                'message' => $exception->getMessage(),
            ]);

            return $this->result('failed', $before, $before, $exception->getMessage());
        }

        return $this->result('skipped', $before, $before, 'Unsupported media type.');
    }

    protected function optimizeImage(string $path, string $mime, string $extension, int $before): array
    {
        if (! function_exists('imagecreatefromstring')) {
            return $this->result('skipped', $before, $before, 'PHP GD is not available.');
        }

        if ($mime === 'image/gif') {
            return $this->result('skipped', $before, $before, 'Animated GIF files are left unchanged.');
        }

        $contents = file_get_contents($path);
        $source = $contents !== false ? @imagecreatefromstring($contents) : false;
        if (! $source) {
            return $this->result('skipped', $before, $before, 'The image could not be decoded.');
        }

        $width = imagesx($source);
        $height = imagesy($source);
        $maxWidth = max(320, (int) config('media.images.max_width', 1920));
        $maxHeight = max(320, (int) config('media.images.max_height', 1080));
        $scale = min(1, $maxWidth / max(1, $width), $maxHeight / max(1, $height));
        $targetWidth = max(1, (int) floor($width * $scale));
        $targetHeight = max(1, (int) floor($height * $scale));

        $canvas = imagecreatetruecolor($targetWidth, $targetHeight);
        if (! $canvas) {
            imagedestroy($source);
            return $this->result('failed', $before, $before, 'Unable to allocate image canvas.');
        }

        if (in_array($mime, ['image/png', 'image/webp'], true)) {
            imagealphablending($canvas, false);
            imagesavealpha($canvas, true);
            $transparent = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
            imagefilledrectangle($canvas, 0, 0, $targetWidth, $targetHeight, $transparent);
        }

        imagecopyresampled($canvas, $source, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);
        $temporary = $this->temporaryPath($path, $extension ?: 'img');
        $written = match ($mime) {
            'image/jpeg' => $this->writeJpeg($canvas, $temporary),
            'image/png' => imagepng($canvas, $temporary, min(9, max(0, (int) config('media.images.png_compression', 8)))),
            'image/webp' => function_exists('imagewebp')
                ? imagewebp($canvas, $temporary, min(100, max(1, (int) config('media.images.webp_quality', 78))))
                : false,
            default => false,
        };

        imagedestroy($canvas);
        imagedestroy($source);

        if (! $written || ! is_file($temporary)) {
            @unlink($temporary);
            return $this->result('skipped', $before, $before, 'The optimized image could not be written.');
        }

        return $this->replaceWhenSmaller($path, $temporary, $before);
    }

    protected function writeJpeg($image, string $path): bool
    {
        imageinterlace($image, true);

        return imagejpeg(
            $image,
            $path,
            min(100, max(1, (int) config('media.images.jpeg_quality', 78))),
        );
    }

    protected function optimizeVideo(string $path, string $extension, int $before): array
    {
        $binary = (string) config('media.videos.ffmpeg_binary', 'ffmpeg');
        if (! $this->commandExists($binary)) {
            return $this->result('skipped', $before, $before, 'FFmpeg is not installed.');
        }

        $extension = in_array($extension, ['mp4', 'm4v', 'mov', 'webm'], true) ? $extension : 'mp4';
        $temporary = $this->temporaryPath($path, $extension);
        $maxWidth = max(480, (int) config('media.videos.max_width', 1280));
        $audioBitrate = (string) config('media.videos.audio_bitrate', '64k');
        $videoFilter = "scale='min({$maxWidth},iw)':-2";

        if ($extension === 'webm') {
            $command = [
                $binary, '-y', '-i', $path,
                '-map_metadata', '-1',
                '-vf', $videoFilter,
                '-c:v', 'libvpx-vp9', '-crf', '38', '-b:v', '0', '-deadline', 'good', '-cpu-used', '4',
                '-c:a', 'libopus', '-b:a', $audioBitrate,
                $temporary,
            ];
        } else {
            $command = [
                $binary, '-y', '-i', $path,
                '-map_metadata', '-1',
                '-vf', $videoFilter,
                '-c:v', 'libx264', '-preset', 'veryfast', '-crf', (string) config('media.videos.crf', 30),
                '-pix_fmt', 'yuv420p',
                '-c:a', 'aac', '-b:a', $audioBitrate,
                '-movflags', '+faststart',
                $temporary,
            ];
        }

        $process = new Process($command);
        $process->setTimeout(max(30, (int) config('media.videos.timeout', 180)));
        $process->run();

        if (! $process->isSuccessful() || ! is_file($temporary)) {
            @unlink($temporary);
            $message = trim($process->getErrorOutput()) ?: 'FFmpeg could not optimize the video.';
            return $this->result('failed', $before, $before, mb_substr($message, 0, 500));
        }

        return $this->replaceWhenSmaller($path, $temporary, $before);
    }

    protected function commandExists(string $binary): bool
    {
        $process = new Process([$binary, '-version']);
        $process->setTimeout(10);
        $process->run();

        return $process->isSuccessful();
    }

    protected function replaceWhenSmaller(string $original, string $temporary, int $before): array
    {
        clearstatcache(true, $temporary);
        $after = is_file($temporary) ? (int) filesize($temporary) : $before;

        if ($after <= 0 || $after >= $before) {
            @unlink($temporary);
            return $this->result('unchanged', $before, $before, 'The original file was already smaller.');
        }

        $permissions = @fileperms($original);
        if (! @rename($temporary, $original)) {
            if (! @copy($temporary, $original)) {
                @unlink($temporary);
                return $this->result('failed', $before, $before, 'Unable to replace the original file.');
            }
            @unlink($temporary);
        }

        if ($permissions !== false) {
            @chmod($original, $permissions & 0777);
        }

        clearstatcache(true, $original);
        $finalSize = (int) filesize($original);

        return $this->result('optimized', $before, $finalSize, null);
    }

    protected function temporaryPath(string $path, string $extension): string
    {
        return dirname($path).'/.'.pathinfo($path, PATHINFO_FILENAME).'-optimized-'.bin2hex(random_bytes(4)).'.'.$extension;
    }

    protected function detectMime(string $path): string
    {
        if (function_exists('mime_content_type')) {
            return (string) (mime_content_type($path) ?: 'application/octet-stream');
        }

        return 'application/octet-stream';
    }

    protected function result(string $status, int $before, int $after, ?string $message): array
    {
        return [
            'status' => $status,
            'before' => $before,
            'after' => $after,
            'saved' => max(0, $before - $after),
            'message' => $message,
        ];
    }
}
