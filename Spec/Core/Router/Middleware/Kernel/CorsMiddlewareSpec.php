<?php

namespace Spec\Minds\Core\Router\Middleware\Kernel;

use Minds\Core\Router\Middleware\Kernel\CorsMiddleware;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class CorsMiddlewareSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(CorsMiddleware::class);
    }
}
