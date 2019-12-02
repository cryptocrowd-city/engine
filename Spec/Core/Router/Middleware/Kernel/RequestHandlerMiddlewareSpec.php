<?php

namespace Spec\Minds\Core\Router\Middleware\Kernel;

use Minds\Core\Router\Middleware\Kernel\RequestHandlerMiddleware;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class RequestHandlerMiddlewareSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(RequestHandlerMiddleware::class);
    }
}
