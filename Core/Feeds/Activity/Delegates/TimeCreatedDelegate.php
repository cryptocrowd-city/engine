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
     * @param $entitie
     * @param string $time_created
     * @return bool
     */
    public function onAdd($entitie, $time_created, $time_sent)
    {
        $this->validate($entitie, $time_created, $time_sent);
        return true;
    }

    /**
     * Validates time_created date and set it to activity
     * @param $entitie
     * @param string $time_created
     * @return bool
     */
    public function onUpdate($entitie, $time_created, $time_sent)
    {
        $this->validate($entitie, $time_created, $time_sent);
        return true;
    }


    private function validate($entitie, $time_created, $time_sent)
    {
        if ($time_created > strtotime('+3 Months')) {
            throw new \InvalidParameterException();
        }

        if ($time_created < strtotime('+5 Minutes')) {
            $time_created = $time_sent;
        }

        $entitie->setTimeCreated($time_created);
        $entitie->setTimeSent($time_sent);
    }

}
