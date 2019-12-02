<?php

namespace Spec\Minds\Core\Router\Middleware\Kernel;

use Minds\Core\Router\Middleware\Kernel\ContentNegotiationMiddleware;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ContentNegotiationMiddlewareSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(ContentNegotiationMiddleware::class);
    }
}
