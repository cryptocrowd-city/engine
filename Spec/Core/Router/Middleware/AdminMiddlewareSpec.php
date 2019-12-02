<?php

namespace Spec\Minds\Core\Router\Middleware;

use Minds\Core\Router\Middleware\AdminMiddleware;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class AdminMiddlewareSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(AdminMiddleware::class);
    }
}
