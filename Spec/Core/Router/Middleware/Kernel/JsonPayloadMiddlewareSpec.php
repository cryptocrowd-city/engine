<?php

namespace Spec\Minds\Core\Router\Middleware\Kernel;

use Minds\Core\Router\Middleware\Kernel\JsonPayloadMiddleware;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class JsonPayloadMiddlewareSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(JsonPayloadMiddleware::class);
    }
}
