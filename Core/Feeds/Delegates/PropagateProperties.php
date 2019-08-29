<?php

namespace Minds\Core\Feeds\Delegates;

use Minds\Core\Entities\Propagator\Properties;
use Minds\Entities\Activity;

class PropagateProperties extends Properties
{
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
