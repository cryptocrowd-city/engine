<?php
/**
 * ExceptionHandlingMiddleware
 * @author edgebal
 */

namespace Minds\Core\Router\Middleware;

use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\JsonResponse;

class ExceptionHandlingMiddleware implements MiddlewareInterface
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
        try {
            return $handler
                ->handle($request);
        } catch (Exception $e) {
            // TODO: Handle Sentry

            return new JsonResponse([
                'status' => 'error',
                'message' => 'Internal error'
            ], 500);
        }
    }
}
