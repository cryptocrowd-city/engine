<?php
/**
 * Router
 * @author edgebal
 */

namespace Minds\Core;

use Minds\Core\Di\Di;
use Minds\Core\Router\Dispatcher;
use Minds\Core\Router\Middleware\ExceptionHandlingMiddleware;
use Minds\Core\Router\Middleware\FrameDenyMiddleware;
use Minds\Core\Router\Middleware\PostFixMiddleware;
use Minds\Core\Router\Middleware\PrePsr7Middleware;
use Minds\Core\Router\Middleware\ModuleRoutingMiddleware;
use Zend\Diactoros\ServerRequestFactory;
use Zend\Diactoros\Uri;

class Router
{
    public function route($uri = null, $method = null, $host = null)
    {
        if (!$uri) {
            $uri = strtok($_SERVER['REDIRECT_ORIG_URI'] ?? $_SERVER['REQUEST_URI'], '?');
        }

        if (!$method) {
            $method = $_SERVER['REQUEST_METHOD'];
        }

        if (!$host) {
            $host = $_SERVER['HTTP_HOST'];
        }

        /** @var Dispatcher $dispatcher */
        $dispatcher = Di::_()->get('Router');

        $dispatcher
            ->pipe(new ExceptionHandlingMiddleware())
            ->pipe(new PostFixMiddleware())
            ->pipe(new FrameDenyMiddleware())
            ->pipe(new ModuleRoutingMiddleware())
            ->pipe(new PrePsr7Middleware() /* !!! WARNING !!! This one _exits PHP_ when path matches */);

        $request = ServerRequestFactory::fromGlobals()
            ->withMethod($method)
            ->withUri(
                (new Uri($uri))
                    ->withHost($host)
            ); // TODO: Ensure it works with reverse proxy

        $response = $dispatcher
            ->handle($request);

        foreach ($response->getHeaders() as $header => $values) {
            foreach ($values as $value) {
                header(sprintf('%s: %s', $header, $value), false);
            }
        }

        http_response_code($response->getStatusCode());
        echo $response->getBody();
    }
}
