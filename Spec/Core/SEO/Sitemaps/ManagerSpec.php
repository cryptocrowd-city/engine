<?php

namespace Spec\Minds\Core\SEO\Sitemaps;

use Minds\Core\SEO\Sitemaps\Manager;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ManagerSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(Manager::class);
    }
}
