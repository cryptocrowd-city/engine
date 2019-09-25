<?php declare(strict_types=1);
/**
 * Manager
 * @author edgebal
 */

namespace Minds\Core\Router;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Manager implements RequestHandlerInterface
{
    /** @var MiddlewareInterface[] */
    protected $middleware = [];

    /**
     * @param MiddlewareInterface $middleware
     * @return $this
     */
    public function pipe(MiddlewareInterface $middleware): self
    {
        $this->middleware[] = $middleware;
        return $this;
    }

    /**
     * Handles a request and produces a response.
     *
     * May call other collaborating code to generate the response.
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if (count($this->middleware) === 0) {
            die('TBD: Fallback');
        }

        $middleware = array_shift($this->middleware);
        return $middleware->process($request, $this);
    }
}
