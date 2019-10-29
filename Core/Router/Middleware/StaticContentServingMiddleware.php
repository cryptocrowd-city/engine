<?php
/**
 * StaticContentServingMiddleware
 * @author edgebal
 */

namespace Minds\Core\Router\Middleware;

use Minds\Core\I18n\I18n;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\HtmlResponse;

class StaticContentServingMiddleware implements MiddlewareInterface
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
        $accept = array_map([$this, '_ditchAcceptAttributes'], explode(',', implode(',', $request->getHeader('Accept'))));

        if (in_array('text/html', $accept, true)) {
            ob_clean();
            ob_start();
            (new I18n())->serveIndex();

            $html = ob_get_contents();
            ob_end_clean();

            return new HtmlResponse($html, 200, [
                'X-Router' => 'static-content-serving'
            ]);
        }

        return $handler
            ->handle($request);
    }

    protected function _ditchAcceptAttributes($value)
    {
        $fragments = explode(';', $value);
        return $fragments[0];
    }
}
