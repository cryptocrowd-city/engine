<?php

namespace Spec\Minds\Core\Router\Middleware;

use Minds\Core\Router\Middleware\LoggedInMiddleware;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class LoggedInMiddlewareSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(LoggedInMiddleware::class);
    }
}
