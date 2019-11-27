<?php declare(strict_types=1);
/**
 * Router
 * @author edgebal
 */

namespace Minds\Core;

use Minds\Core\Di\Di;
use Minds\Core\Router\Dispatcher;
use Minds\Core\Router\Middleware\Kernel;
use Zend\Diactoros\ServerRequestFactory;
use Zend\Diactoros\Uri;

class Router
{
    /** @var Dispatcher */
    protected $dispatcher;

    /**
     * Router constructor.
     * @param Dispatcher $dispatcher
     */
    public function __construct(
        $dispatcher = null
    )
    {
        $this->dispatcher = $dispatcher ?: Di::_()->get('Router');
    }

    /**
     * @param string|null $uri
     * @param string|null $method
     * @param string|null $host
     */
    public function route(string $uri = null, string $method = null, string $host = null): void
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

        $request = ServerRequestFactory::fromGlobals()
            ->withMethod($method)
            ->withUri(
                (new Uri($uri))
                    ->withHost($host)
            ); // TODO: Ensure it works with reverse proxy

        $response = $this->dispatcher
            ->pipe(new Kernel\ContentNegotiationMiddleware())
            ->pipe(new Kernel\ErrorHandlerMiddleware())
            ->pipe(
                (new Kernel\RouteResolverMiddleware())
                    ->setAttributeName('_request-handler')
            ) // Note: Pre-PSR7 routes will not advance further than this
            ->pipe(new Kernel\CorsMiddleware())
            ->pipe(new Kernel\JsonPayloadMiddleware())
            ->pipe(new Kernel\FrameSecurityMiddleware())
            ->pipe(
                (new Kernel\SessionMiddleware())
                    ->setAttributeName('_user')
            )
            ->pipe(
                (new Kernel\OauthMiddleware())
                    ->setAttributeName('_user')
            )
            ->pipe(new Kernel\XsrfCookieMiddleware())
            ->pipe(
                (new Kernel\RequestHandlerMiddleware())
                    ->setAttributeName('_request-handler')
            )
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
