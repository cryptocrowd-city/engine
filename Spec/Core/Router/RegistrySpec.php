<?php

namespace Spec\Minds\Core\Router;

use Minds\Core\Router\Registry;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class RegistrySpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(Registry::class);
    }
}
