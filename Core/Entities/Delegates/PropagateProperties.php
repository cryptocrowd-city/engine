<?php

namespace Minds\Core\Entities\Delegates;

use Minds\Core\Entities\Propagator\Properties;
use Minds\Entities\Activity;

class PropagateProperties extends Properties
{
    public function toActivity($from, Activity $to): Activity
    {
        if ($this->valueHasChanged($from->getNsfw(), $to->getNsfw())) {
            $to->setNsfw($from->getNsfw());
        }

        if ($this->valueHasChanged($from->getNsfwLock(), $to->getNsfwLock())) {
            $to->setNsfwLock($from->getNsfwLock());
        }

        return $to;
    }

    public function fromActivity(Activity $from, $to)
    {
        if ($this->valueHasChanged($from->getNsfw(), $to->getNsfw())) {
            $to->setNsfw($from->getNsfw());
        }

        if ($this->valueHasChanged($from->getNsfwLock(), $to->getNsfwLock())) {
            $to->setNsfwLock($from->getNsfwLock());
        }

        return $to;
    }
}
