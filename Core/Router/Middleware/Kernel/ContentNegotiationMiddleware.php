<?php
/**
 * ContentNegotiationMiddleware
 * @author edgebal
 */

namespace Minds\Core\Router\Middleware\Kernel;

use Minds\Core\Router\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\JsonResponse;

class ContentNegotiationMiddleware implements MiddlewareInterface
{
    /** @var string[] */
    const JSON_MIME_TYPES = ['application/json', 'text/json', 'application/x-json'];

    /** @var string[] */
    const HTML_MIME_TYPES = ['text/html', 'application/xhtml+xml'];

    /**
     * Process an incoming server request.
     *
     * Processes an incoming server request in order to produce a response.
     * If unable to produce the response itself, it may delegate to the provided
     * request handler to do so.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $accept = array_map([$this, '_normalizeAcceptEntries'], explode(',', implode(',', $request->getHeader('Accept'))));

        if (array_intersect($accept, static::JSON_MIME_TYPES)) {
            $request = $request
                ->withAttribute('accept', 'json');
        } elseif (array_intersect($accept, static::HTML_MIME_TYPES)) {
            $request = $request
                ->withAttribute('accept', 'html');
        }

        return $handler
            ->handle($request);
    }

    /**
     * @param $value
     * @return mixed
     */
    protected function _normalizeAcceptEntries(string $value): string
    {
        $fragments = explode(';', $value);
        return $fragments[0];
    }
}
