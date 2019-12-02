<?php

namespace Spec\Minds\Core\Router\Middleware\Kernel;

use Minds\Core\Router\Middleware\Kernel\RegistryEntryMiddleware;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class RegistryEntryMiddlewareSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(RegistryEntryMiddleware::class);
    }
}
