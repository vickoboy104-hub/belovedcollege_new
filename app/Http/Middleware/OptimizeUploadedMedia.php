<?php

namespace App\Http\Middleware;

use App\Services\MediaOptimizer;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class OptimizeUploadedMedia
{
    public function __construct(protected MediaOptimizer $optimizer)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isMethod('POST') || $request->isMethod('PUT') || $request->isMethod('PATCH')) {
            $this->walkFiles($request->allFiles());
        }

        return $next($request);
    }

    protected function walkFiles(array $files): void
    {
        foreach ($files as $file) {
            if (is_array($file)) {
                $this->walkFiles($file);
                continue;
            }

            if (! $file instanceof UploadedFile || ! $file->isValid()) {
                continue;
            }

            try {
                $this->optimizer->optimizeUploadedFile($file);
            } catch (Throwable $exception) {
                report($exception);
            }
        }
    }
}
