<?php
/**
 * TimeCreatedDelegate
 * @author juanmsolaro
 */

namespace Minds\Core\Feeds\Activity\Delegates;

use Minds\Core\Di\Di;
use Minds\Core\Entities\Actions\Save;
use Minds\Entities\Activity;

class TimeCreatedDelegate
{

    /** @var Save */
    protected $save;

    /**
     * TimeCreatedDelegate constructor.
     * @param Save $save
     */
    public function __construct(
        $save = null
    )
    {
        $this->save = $save ?: new Save();
    }

    /**
     * Validates time_created date and set it to activity
     * @param $entity
     * @param string $time_created
     * @return bool
     */
    public function onAdd($entity, $time_created, $time_sent)
    {
        $this->validate($entity, $time_created, $time_sent);
        return true;
    }

    /**
     * Validates time_created date and set it to activity
     * @param $entity
     * @param string $time_created
     * @return bool
     */
    public function onUpdate($entity, $time_created, $time_sent)
    {
        $this->validate($entity, $time_created, $time_sent);
        return true;
    }


    private function validate($entity, $time_created, $time_sent)
    {
        if ($time_created > strtotime('+3 Months')) {
            throw new \InvalidParameterException();
        }

        if ($time_created < strtotime('+5 Minutes')) {
            $time_created = $time_sent;
        }

        $entity->setTimeCreated($time_created);
        $entity->setTimeSent($time_sent);
    }

}
