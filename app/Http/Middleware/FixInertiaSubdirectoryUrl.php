<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class FixInertiaSubdirectoryUrl
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $baseUrl = $request->getBaseUrl(); // Ej. "/heladeria/public"
        if (empty($baseUrl)) {
            return $response;
        }

        $doubleBase = $baseUrl . $baseUrl; // Ej. "/heladeria/public/heladeria/public"

        if ($request->header('X-Inertia')) {
            // Respuesta JSON de Inertia (AJAX)
            if (method_exists($response, 'getData') && $data = $response->getData(true)) {
                if (isset($data['url']) && str_starts_with($data['url'], $doubleBase)) {
                    $data['url'] = substr($data['url'], strlen($baseUrl));
                    $response->setData($data);
                }
            }
        } else {
            // Respuesta inicial HTML
            $content = $response->getContent();
            if (is_string($content) && str_contains($content, 'data-page="')) {
                $content = preg_replace_callback('/data-page="([^"]+)"/', function ($matches) use ($baseUrl, $doubleBase) {
                    $pageJson = html_entity_decode($matches[1]);
                    $page = json_decode($pageJson, true);
                    if ($page && isset($page['url']) && str_starts_with($page['url'], $doubleBase)) {
                        $page['url'] = substr($page['url'], strlen($baseUrl));
                        return 'data-page="' . e(json_encode($page)) . '"';
                    }
                    return $matches[0];
                }, $content);
                $response->setContent($content);
            }
        }

        return $response;
    }
}
