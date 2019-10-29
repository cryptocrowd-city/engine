<?php
/**
 * ModuleRoutingMiddleware
 * @author edgebal
 */

namespace Minds\Core\Router\Middleware;

use Minds\Core\Di\Di;
use Minds\Core\Router\DiRef;
use Minds\Core\Router\Dispatcher;
use Minds\Core\Router\RouterRegistry;
use mysql_xdevapi\Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ModuleRoutingMiddleware implements MiddlewareInterface
{
    /** @var RouterRegistry */
    protected $routerRegistry;

    /**
     * ModuleRoutingMiddleware constructor.
     * @param RouterRegistry $routerRegistry
     */
    public function __construct(
        $routerRegistry = null
    )
    {
        $this->routerRegistry = $routerRegistry ?: Di::_()->get('Router\Registry');
    }

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
        $routerRegistryEntry = $this->routerRegistry->getBestMatch(
            strtolower($request->getMethod()),
            $request->getUri()->getPath()
        );

        if ($routerRegistryEntry) {

            // Setup sub-router

            $dispatcher = new Dispatcher();

            // Pipe route-specific middleware

            foreach ($routerRegistryEntry->getMiddleware() as $middleware) {
                if (!class_exists($middleware)) {
                    throw new Exception("{$middleware} does not exist");
                }

                $middlewareInstance = new $middleware;

                if (!($middlewareInstance instanceof MiddlewareInterface)) {
                    throw new Exception("{$middleware} is not a middleware");
                }

                $dispatcher->pipe($middlewareInstance);
            }

            // Dispatch middleware

            return $dispatcher
                ->pipe(new RouterRegistryEntryMiddleware())
                ->handle(
                    $request
                        ->withAttribute('router_registry_entry', $routerRegistryEntry)
                )
                ->withHeader('X-Router', 'module-routing');

        }

        // ... or pass down to next handler

        return $handler
            ->handle($request);
    }
}
