<?php

namespace Minds\Core\Entities;

use Minds\Core\Data\Call;
use Minds\Core\Di\Di;
use Minds\Core\Entities\Actions\Save;
use Minds\Core\Entities\Propagator\Properties;
use Minds\Core\EntitiesBuilder;
use Minds\Entities\Activity;
use Minds\Core;

class PropagateProperties
{
    /**  @var Properties[] */
    protected $propagators;
    /** @var Call */
    private $db;
    /** @var Save */
    private $save;
    /** @var EntitiesBuilder */
    private $entitiesBuilder;
    /** @var bool */
    private $changed = false;

    public function __construct(Call $db = null, Save $save = null, EntitiesBuilder $entitiesBuilder = null)
    {
        $this->db = $db ?? new Call('entities_by_time');
        $this->save = $save ?? new Save();
        $this->entitiesBuilder = $entitiesBuilder ?? Di::_()->get('EntitiesBuilder');
        $this->registerPropagators();
    }

    protected function registerPropagators(): void
    {
        /* Register PropertyPropagator classes here */
        $this->addPropagator(Core\Blogs\Delegates\PropagateProperties::class);
        $this->addPropagator(Core\Feeds\Delegates\PropagateProperties::class);
        $this->addPropagator(Core\Media\Delegates\PropagateProperties::class);
        $this->addPropagator(Core\Entities\Delegates\PropagateProperties::class);
        $this->addPropagator(Core\Permissions\Delegates\PropagateProperties::class);
    }

    protected function addPropagator(string $class): void
    {
        $obj = new $class();
        if (!$obj instanceof Properties) {
            throw new \Exception('Propagator class is not a Property Propagator');
        }

        $this->propagators[] = $obj;
    }

    public function from($entity): void
    {
        if ($entity instanceof Activity) {
            $this->fromActivity($entity);
        } else {
            $this->toActivities($entity);
        }
    }

    protected function fromActivity(Activity $activity): void
    {
        $this->changed = false;
        $attachment = $this->entitiesBuilder->single($activity->get('entity_guid'));
        if ($attachment === false) {
            return;
        }

        foreach ($this->propagators as $propagator) {
            if ($propagator->willActOnEntity($attachment)) {
                $propagator->fromActivity($activity, $attachment);
                $this->changed |= $propagator->changed();
            }
        }

        if ($this->changed) {
            $this->save->setEntity($attachment)->save();
        }
    }

    protected function toActivities($entity): void
    {
        $activities = $this->getActivitiesForEntity($entity->getGuid());
        foreach ($activities as $activity) {
            $this->propagateToActivity($entity, $activity);
            if ($this->changed) {
                $this->save->setEntity($activity)->save();
            }
        }
    }

    /**
     * @param string $entityGuid
     * @return Activity[]
     */
    private function getActivitiesForEntity(string $entityGuid): array
    {
        $activities = [];

        foreach ($this->db->getRow("activity:entitylink:{$entityGuid}") as $activityGuid => $ts) {
            $activities[] = $this->entitiesBuilder->single($activityGuid);
        }

        return $activities;
    }

    public function propagateToActivity($entity, Activity &$activity): void
    {
        $this->changed = false;
        foreach ($this->propagators as $propagator) {
            if ($propagator->willActOnEntity($entity)) {
                $propagator->toActivity($entity, $activity);
                $this->changed |= $propagator->changed();
            }
        }
    }
}
