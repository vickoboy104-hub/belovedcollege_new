<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InjectReportPrintAssets
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! $request->isMethod('GET')
            || ! $request->routeIs('admin.reports.print', 'portal.results.print')
            || ! method_exists($response, 'getContent')
            || ! method_exists($response, 'setContent')) {
            return $response;
        }

        $contentType = strtolower((string) $response->headers->get('Content-Type', ''));
        if (! str_contains($contentType, 'text/html')) {
            return $response;
        }

        $html = $response->getContent();
        if (! is_string($html) || ! str_contains(strtolower($html), '<body')) {
            return $response;
        }

        $classic = $request->string('layout')->toString() === 'classic';
        $bodyClass = $classic ? 'report-print-classic' : 'report-print-modern';
        $stylesheets = $classic
            ? ['report-print-classic.css']
            : ['report-print-modern.css', 'report-print-modern-flow-fix.css'];

        $html = preg_replace_callback('/<body\b[^>]*>/i', function (array $match) use ($bodyClass): string {
            $tag = $match[0];

            if (preg_match('/\bclass\s*=\s*(["\'])(.*?)\1/i', $tag)) {
                return preg_replace_callback(
                    '/\bclass\s*=\s*(["\'])(.*?)\1/i',
                    fn (array $classMatch): string => 'class='.$classMatch[1].trim($classMatch[2].' '.$bodyClass).$classMatch[1],
                    $tag,
                    1,
                ) ?? $tag;
            }

            return preg_replace('/>$/', ' class="'.$bodyClass.'">', $tag) ?? $tag;
        }, $html, 1) ?? $html;

        foreach ($stylesheets as $stylesheet) {
            if (str_contains($html, $stylesheet)) {
                continue;
            }

            $version = $stylesheet === 'report-print-modern-flow-fix.css'
                ? '20260719-report-print-flow-1'
                : '20260713-report-print-1';
            $link = '<link rel="stylesheet" href="'.e(asset($stylesheet)).'?v='.$version.'">';
            $html = preg_replace('/<\/head>/i', $link.'</head>', $html, 1) ?? $html;
        }

        $response->setContent($html);

        return $response;
    }
}
