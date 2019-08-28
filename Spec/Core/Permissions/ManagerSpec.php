<?php

namespace Spec\Minds\Core\Permissions;

use Minds\Core\Entities\PropagateProperties;
use Minds\Core\Permissions\Manager;
use Minds\Core\Permissions\Permissions;
use Minds\Entities\User;
use PhpSpec\ObjectBehavior;
use Minds\Core\Entities\Actions\Save;
use Minds\Entities\Entity;
use Minds\Entities\Image;

class ManagerSpec extends ObjectBehavior
{
    /** @var User */
    protected $user;
    /** @var Save */
    protected $save;
    /** @var PropagateProperties */
    protected $propagateProperties;

    public function let(
        Save $save,
        PropagateProperties $propagateProperties
    ) {
        $this->save = $save;
        $this->propagateProperties = $propagateProperties;
        $this->beConstructedWith($this->save, $this->propagateProperties);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(Manager::class);
    }

    public function it_should_save_entity_permissions(Entity $entity, Image $image)
    {
        $permissions = new Permissions();
        $entity->setAllowComments(true)->shouldBeCalled();
        $this->save->setEntity($entity)->shouldBeCalled()->willReturn($this->save);
        $this->save->save()->shouldBeCalled();
        $this->propagateProperties->from($entity)->shouldBeCalled();
        $this->save($entity, $permissions);
    }
}
