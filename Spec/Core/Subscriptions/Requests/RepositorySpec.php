<?php

namespace Spec\Minds\Core\Subscriptions\Requests;

use Minds\Core\Subscriptions\Requests\Repository;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class RepositorySpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(Repository::class);
    }
}
