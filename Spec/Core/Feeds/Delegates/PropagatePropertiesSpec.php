<?php

namespace Spec\Minds\Core\Feeds\Delegates;

use Minds\Entities\Activity;
use Minds\Entities\Entity;
use PhpSpec\ObjectBehavior;

class PropagatePropertiesSpec extends ObjectBehavior
{
    /** @var Entity */
    protected $entity;
    /** @var Activity */
    protected $activity;

    public function let(
        Entity $entity,
        Activity $activity
    )
    {
        $this->entity = $entity;
        $this->activity = $activity;
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType('Minds\Core\Feeds\Delegates\PropagateProperties');
    }

    public function it_should_propagate_changes_to_activity()
    {
        $this->entity->getModeratorGuid()->shouldBeCalled()->willReturn('12345');
        $this->activity->getModeratorGuid()->shouldBeCalled()->willReturn('6789');
        $this->activity->setModeratorGuid('12345')->shouldBeCalled();

        $this->entity->getTimeModerated()->shouldBeCalled()->willReturn(12345);
        $this->activity->getTimeModerated()->shouldBeCalled()->willReturn(6789);
        $this->activity->setTimeModerated(12345)->shouldBeCalled();

        $this->toActivity($this->entity, $this->activity);
    }

    public function it_should_propogate_properties_from_activity()
    {
        $this->activity->getModeratorGuid()->shouldBeCalled()->willReturn('12345');
        $this->entity->getModeratorGuid()->shouldBeCalled()->willReturn('6789');
        $this->entity->setModeratorGuid('12345')->shouldBeCalled();

        $this->activity->getTimeModerated()->shouldBeCalled()->willReturn(12345);
        $this->entity->getTimeModerated()->shouldBeCalled()->willReturn(6789);
        $this->entity->setTimeModerated(12345)->shouldBeCalled();

        $this->fromActivity($this->activity, $this->entity);
    }
}
