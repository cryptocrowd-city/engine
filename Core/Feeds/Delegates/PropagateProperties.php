<?php

namespace Minds\Core\Feeds\Delegates;

use Minds\Core\Entities\Propagator\Properties;
use Minds\Entities\Activity;

class PropagateProperties extends Properties
{
    public function toActivity($from, Activity $to): Activity
    {
        if ($this->valueHasChanged($from->getModeratorGuid(), $to->getModeratorGuid())) {
            $to->setModeratorGuid($from->getModeratorGuid());
        }

        if ($this->valueHasChanged($from->getTimeModerated(), $to->getTimeModerated())) {
            $to->setTimeModerated($from->getTimeModerated());
        }

        return $to;
    }

    public function fromActivity(Activity $from, $to)
    {
        if ($this->valueHasChanged($from->getModeratorGuid(), $to->getModeratorGuid())) {
            $to->setModeratorGuid($from->getModeratorGuid());
        }

        if ($this->valueHasChanged($from->getTimeModerated(), $to->getTimeModerated())) {
            $to->setTimeModerated($from->getTimeModerated());
        }

        return $to;
    }
}
