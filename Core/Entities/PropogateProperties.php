<?php

namespace Minds\Core\Entities;

use Minds\Core\Data\Call;
use Minds\Core\Di\Di;
use Minds\Core\Entities\Actions\Save;
use Minds\Core\Entities\Propogator\Properties;
use Minds\Core\EntitiesBuilder;
use Minds\Entities\Activity;
use Minds\Core;

class PropogateProperties
{
    /**  @var Properties[] */
    protected $propogators;
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
        $this->registerPropogators();
    }

    protected function registerPropogators(): void
    {
        /* Register PropertyPropogator classes here */
        $this->addPropogator(PropogateBlogProperties::class);
    }

    protected function addPropogator(string $class): void
    {
        $obj = new $class();
        if (!$obj instanceof Properties)
            throw new \Exception('Propogator class is not a Property Propogator');

        $this->propogators[] = $obj;
    }

    public function from($entity): void
    {
        if ($entity instanceof Activity)
            $this->fromActivity($entity);
        else
            $this->toActivities($entity);
    }

    protected function fromActivity(Activity $activity): void
    {
        $this->changed = false;
        $attachment = $this->entitiesBuilder->single($activity->get('entity_guid'));
        if ($attachment === false)
            return;

        foreach ($this->propogators as $propogator) {
            if ($propogator->willActOnEntity($attachment)) {
                $propogator->fromActivity($activity, $attachment);
                $this->changed |= $propogator->changed();
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
            $this->propogateToActivity($entity, $activity);
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

    public function propogateToActivity($entity, Activity &$activity): void
    {
        $this->changed = false;
        foreach ($this->propogators as $propogator) {
            if ($propogator->willActOnEntity($entity)) {
                $propogator->toActivity($entity, $activity);
                $this->changed |= $propogator->changed();
            }
        }
    }
}
