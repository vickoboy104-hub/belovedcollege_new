<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DeferAutoplayMedia
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! $request->isMethod('GET') || ! method_exists($response, 'getContent') || ! method_exists($response, 'setContent')) {
            return $response;
        }

        $contentType = (string) $response->headers->get('Content-Type', '');
        if (! str_contains(strtolower($contentType), 'text/html')) {
            return $response;
        }

        $html = $response->getContent();
        if (! is_string($html) || ! str_contains(strtolower($html), '<video')) {
            return $response;
        }

        $html = preg_replace_callback('/<video\b[^>]*>/i', function (array $match): string {
            $tag = $match[0];
            if (! preg_match('/\sautoplay(?:\s*=\s*(?:"[^"]*"|\'[^\']*\'|[^\s>]+))?/i', $tag)) {
                return $tag;
            }

            $tag = preg_replace('/\sautoplay(?:\s*=\s*(?:"[^"]*"|\'[^\']*\'|[^\s>]+))?/i', '', $tag) ?? $tag;

            if (preg_match('/\spreload\s*=\s*(?:"[^"]*"|\'[^\']*\'|[^\s>]+)/i', $tag)) {
                $tag = preg_replace('/\spreload\s*=\s*(?:"[^"]*"|\'[^\']*\'|[^\s>]+)/i', ' preload="none"', $tag) ?? $tag;
            } else {
                $tag = preg_replace('/>$/', ' preload="none">', $tag) ?? $tag;
            }

            if (! str_contains($tag, 'data-deferred-autoplay')) {
                $tag = preg_replace('/>$/', ' data-deferred-autoplay>', $tag) ?? $tag;
            }

            return $tag;
        }, $html) ?? $html;

        if (str_contains($html, 'data-deferred-autoplay') && ! str_contains($html, 'deferred-media.js')) {
            $script = '<script src="'.e(asset('deferred-media.js')).'?v=20260711-media-1" defer></script>';
            $html = str_contains(strtolower($html), '</body>')
                ? preg_replace('/<\/body>/i', $script.'</body>', $html, 1) ?? $html
                : $html.$script;
        }

        $response->setContent($html);

        return $response;
    }
}
