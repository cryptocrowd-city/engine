<?php

namespace Spec\Minds\Core\Router;

use Minds\Core\Router\Dispatcher;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class DispatcherSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(Dispatcher::class);
    }
}
