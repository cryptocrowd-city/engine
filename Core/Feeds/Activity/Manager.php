<?php
/**
 * Manager
 * @author edgebal
 */

namespace Minds\Core\Feeds\Activity;

use Minds\Entities\Activity;
use Zend\Diactoros\ServerRequest;

class Manager
{
    public function add(ServerRequest $request)
    {
        throw new \NotImplementedException();
    }

    public function update(ServerRequest $request)
    {
        throw new \NotImplementedException();
    }

    public function delete(ServerRequest $request)
    {
        throw new \NotImplementedException();
    }

    /**
     * @param \ElggEntity $entity
     * @return Activity
     */
    public function createFromEntity($entity): Activity
    {
        $activity = new Activity();
        $activity->setTimeCreated(time());
        $activity->setTimeSent(time());
        $activity->setTitle($entity->title);
        $activity->setMessage($entity->description);
        $activity->setFromEntity($entity);
        $activity->access_id = $entity->access_id;

        if ($entity->type === 'object' && in_array($entity->subtype, ['image', 'video'], true)) {
            $activity->setCustom($entity->getActivityParameters());
        }

        return $activity;
    }
}
