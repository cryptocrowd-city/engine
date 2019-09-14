<?php

namespace Spec\Minds\Core\Permissions;

use Minds\Core\Permissions\Permissions;
use Minds\Core\Permissions\Roles\Roles;
use Minds\Entities\User;
use Minds\Entities\Activity;
use Minds\Entities\Group;
use Minds\Core\EntitiesBuilder;
use PhpSpec\ObjectBehavior;
use Prophecy\Prophet;
use Minds\Common\Access;
use Minds\Exceptions\ImmutableException;

class PermissionsSpec extends ObjectBehavior
{
    /** @var EntitiesBuilder */
    private $entitiesBuilder;

    
    public function let(EntitiesBuilder $entitiesBuilder)
    {
        $this->entitiesBuilder = $entitiesBuilder;
        $this->beConstructedWith($this->user, null, $this->entitiesBuilder);
    }

    public function it_should_except_with_no_user()
    {
        $this->shouldThrow(new InvalidArgumentException('Entity is not a user'))
            ->duringGetList();
    }
}
