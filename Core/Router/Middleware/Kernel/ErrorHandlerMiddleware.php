<?php
/**
 * ErrorHandlerMiddleware
 * @author edgebal
 */

namespace Minds\Core\Router\Middleware\Kernel;

use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\JsonResponse;

class ErrorHandlerMiddleware implements MiddlewareInterface
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

            error_log($e);
            // TODO: Nicer logging

            $message = 'Internal Server Error';
            $status = 500;

            switch ($request->getAttribute('accept')) {
                case 'html':
                    return new HtmlResponse(sprintf('<h1>%s</h1>', $message), $status);

                case 'json':
                default:
                    return new JsonResponse([
                        'status' => 'error',
                        'message' => $message,
                    ], $status);
            }
        }
    }
}
