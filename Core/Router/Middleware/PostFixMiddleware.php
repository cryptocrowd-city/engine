<?php
/**
 * PostFixMiddleware
 * @author edgebal
 */

namespace Minds\Core\Router\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class PostFixMiddleware implements MiddlewareInterface
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
        foreach ($request->getHeader('Content-Type') ?: [] as $contentTypeValue) {
            if (stripos($contentTypeValue, 'application/json') === 0) {
                $request = $request
                    ->withParsedBody(json_decode($request->getBody(), true));
                break;
            }
        }

        return $handler->handle($request);
    }
}
