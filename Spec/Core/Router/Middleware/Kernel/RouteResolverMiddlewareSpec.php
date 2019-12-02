<?php

namespace Spec\Minds\Core\Router\Middleware\Kernel;

use Minds\Core\Router\Middleware\Kernel\RouteResolverMiddleware;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class RouteResolverMiddlewareSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(RouteResolverMiddleware::class);
    }
}
