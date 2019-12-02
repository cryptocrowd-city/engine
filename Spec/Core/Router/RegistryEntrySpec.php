<?php

namespace Spec\Minds\Core\Router;

use Minds\Core\Router\RegistryEntry;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class RegistryEntrySpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(RegistryEntry::class);
    }
}
