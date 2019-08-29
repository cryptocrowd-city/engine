<?php

namespace Minds\Core\Feeds\Delegates;

use Minds\Core\Entities\Propagator\Properties;
use Minds\Entities\Activity;

/**
 * Class PropagateProperties
 * @package Minds\Core\Feeds\Delegates
 */
class PropagateProperties extends Properties
{
    /**
     * Propagate Entity properties to activity
     * @param $from
     * @param Activity $to
     * @return Activity
     */
    public function toActivity($from, Activity $to): Activity
    {
        if ($this->valueHasChanged((int)$from->getModeratorGuid(), (int)$to->getModeratorGuid())) {
            $to->setModeratorGuid((int)$from->getModeratorGuid());
        }

        if ($this->valueHasChanged((int)$from->getTimeModerated(), (int)$to->getTimeModerated())) {
            $to->setTimeModerated((int)$from->getTimeModerated());
        }

        return $to;
    }

    /**
     * Propagate activity properties to entity
     * @param Activity $from
     * @param $to
     * @return mixed
     */
    public function fromActivity(Activity $from, $to)
    {
        if ($this->valueHasChanged((int)$from->getModeratorGuid(), (int)$to->getModeratorGuid())) {
            $to->setModeratorGuid((int)$from->getModeratorGuid());
        }

        if ($this->valueHasChanged((int)$from->getTimeModerated(), (int)$to->getTimeModerated())) {
            $to->setTimeModerated((int)$from->getTimeModerated());
        }

        return $to;
    }
}
