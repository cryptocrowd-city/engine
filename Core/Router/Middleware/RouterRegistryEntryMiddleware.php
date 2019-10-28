<?php
/**
 * RouterRegistryEntryMiddleware
 * @author edgebal
 */

namespace Minds\Core\Router\Middleware;

use Exception;
use Minds\Core\Di\Di;
use Minds\Core\Router\DiRef;
use Minds\Core\Router\RouterRegistryEntry;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RouterRegistryEntryMiddleware implements MiddlewareInterface
{
    /**
     * Process an incoming server request.
     *
     * Processes an incoming server request in order to produce a response.
     * If unable to produce the response itself, it may delegate to the provided
     * request handler to do so.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var RouterRegistryEntry $routerRegistryEntry */
        $routerRegistryEntry = $request->getAttribute('router_registry_entry');

        if ($routerRegistryEntry) {
            $binding = $routerRegistryEntry->getBinding();
            $parameters = $routerRegistryEntry->extract($request->getUri()->getPath());

            if ($binding instanceof DiRef) {
                return call_user_func(
                    [
                        Di::_()->get($binding->getProvider()),
                        $binding->getMethod()
                    ],
                    $request
                        ->withAttribute('parameters', $parameters)
                );
            } else if (is_callable($binding)) {
                return call_user_func(
                    $binding,
                    $request
                        ->withAttribute('parameters', $parameters)
                );
            } else {
                throw new Exception("Invalid router binding");
            }
        }

        return $handler
            ->handle($request);
    }
}
