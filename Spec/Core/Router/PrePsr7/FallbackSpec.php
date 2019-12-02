<?php

namespace Spec\Minds\Core\Router\PrePsr7;

use Minds\Core\Router\PrePsr7\Fallback;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class FallbackSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(Fallback::class);
    }
}
