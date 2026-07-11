<?php

namespace App\Console\Commands;

use App\Services\MediaOptimizer;
use Illuminate\Console\Command;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

class OptimizeUploadedMedia extends Command
{
    protected $signature = 'media:optimize
        {path=public/uploads/settings : Directory containing uploaded media}
        {--images-only : Optimize image files only}
        {--videos-only : Optimize video files only}';

    protected $description = 'Compress existing uploaded images and videos without replacing a file unless the result is smaller';

    public function handle(MediaOptimizer $optimizer): int
    {
        $path = base_path((string) $this->argument('path'));
        if (! is_dir($path)) {
            $this->error("Directory not found: {$path}");
            return self::FAILURE;
        }

        $imageExtensions = ['jpg', 'jpeg', 'png', 'webp'];
        $videoExtensions = ['mp4', 'm4v', 'mov', 'webm'];
        $allowed = match (true) {
            $this->option('images-only') => $imageExtensions,
            $this->option('videos-only') => $videoExtensions,
            default => [...$imageExtensions, ...$videoExtensions],
        };

        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS),
        );

        $count = 0;
        $optimized = 0;
        $saved = 0;

        /** @var SplFileInfo $file */
        foreach ($files as $file) {
            if (! $file->isFile() || ! in_array(strtolower($file->getExtension()), $allowed, true)) {
                continue;
            }

            $count++;
            $result = $optimizer->optimizePath($file->getPathname(), $file->getExtension());
            $relative = str_replace(base_path().DIRECTORY_SEPARATOR, '', $file->getPathname());

            if ($result['status'] === 'optimized') {
                $optimized++;
                $saved += (int) $result['saved'];
                $this->info(sprintf(
                    'Optimized %s: %s → %s',
                    $relative,
                    $this->formatBytes((int) $result['before']),
                    $this->formatBytes((int) $result['after']),
                ));
                continue;
            }

            $message = $result['message'] ? ' — '.$result['message'] : '';
            $this->line("Skipped {$relative}{$message}");
        }

        $this->newLine();
        $this->info(sprintf(
            'Checked %d file(s); optimized %d; saved %s.',
            $count,
            $optimized,
            $this->formatBytes($saved),
        ));

        if (in_array('mp4', $allowed, true) && ! $this->ffmpegAvailable()) {
            $this->warn('FFmpeg was not detected. Images were optimized, but videos require FFmpeg.');
        }

        return self::SUCCESS;
    }

    protected function ffmpegAvailable(): bool
    {
        $binary = escapeshellarg((string) config('media.videos.ffmpeg_binary', 'ffmpeg'));
        $output = [];
        $status = 1;
        @exec("{$binary} -version 2>&1", $output, $status);

        return $status === 0;
    }

    protected function formatBytes(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes.' B';
        }

        $units = ['KB', 'MB', 'GB'];
        $value = $bytes / 1024;
        foreach ($units as $unit) {
            if ($value < 1024 || $unit === 'GB') {
                return number_format($value, $value >= 100 ? 0 : 2).' '.$unit;
            }
            $value /= 1024;
        }

        return $bytes.' B';
    }
}
