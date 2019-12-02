<?php

namespace Spec\Minds\Core;

use Minds\Core\Router;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class RouterSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(Router::class);
    }
}
