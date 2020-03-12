<?php

namespace Spec\Minds\Core\Search\MetricsSync;

use Minds\Core\Search\MetricsSync\Manager;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ManagerSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(Manager::class);
    }
}
