<?php

namespace Spec\Minds\Core\Router\Middleware\Kernel;

use Minds\Core\Router\Middleware\Kernel\FrameSecurityMiddleware;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class FrameSecurityMiddlewareSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(FrameSecurityMiddleware::class);
    }
}
