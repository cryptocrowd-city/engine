<?php

namespace Spec\Minds\Core\Router\Middleware\Kernel;

use Minds\Core\Router\Middleware\Kernel\EmptyResponseMiddleware;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class EmptyResponseMiddlewareSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(EmptyResponseMiddleware::class);
    }
}
