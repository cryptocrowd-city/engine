<?php
/**
 * LegacyRouterMiddleware
 * @author edgebal
 */

namespace Minds\Core\Router\Middleware;

use Minds\Core\Router\PrePsr7;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class PrePsr7Middleware implements MiddlewareInterface
{
    /**
     * Process an incoming server request.
     *
     * Processes an incoming server request in order to produce a response.
     * If unable to produce the response itself, it may delegate to the provided
     * request handler to do so.
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $allowed = [
            '/api/v1/',
            '/api/v2/',
            '/emails/',
            '/fs/v1',
            '/oauth2/',
            '/checkout',
            '/deeplinks',
            '/icon',
            '/sitemap',
            '/sitemaps',
            '/thumbProxy',
            '/archive',
            '/wall',
            '/not-supported',
            '/apple-app-site-association'
        ];

        $route = sprintf("/%s", ltrim($request->getUri()->getPath(), '/'));

        $prePsr7 = false;

        foreach ($allowed as $allowedRoute) {
            if (stripos($route, $allowedRoute) === 0) {
                $prePsr7 = true;
                break;
            }
        }

        if ($route === '/' || $prePsr7) {
            header('X-Router-Mode: legacy');

            (new PrePsr7\Router())
                ->route($route, strtolower($request->getMethod()));

            exit; // NOTE: This is awful, but needed
        }

        return $handler
            ->handle($request);
    }
}
