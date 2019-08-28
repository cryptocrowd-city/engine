<?php

namespace Minds\Core\Entities\Delegates;

use Minds\Core\Entities\Propogator\Properties;
use Minds\Entities\Activity;

class PropogateProperties extends Properties
{
    public function toActivity($from, Activity &$to): void
    {
        if ($this->valueHasChanged($from->getNsfw(), $to->getNsfw())) {
            $to->setNsfw($from->getNsfw());
        }

        if ($this->valueHasChanged($from->getNsfwLock(), $to->getNsfwLock())) {
            $to->setNsfwLock($from->getNsfwLock());
        }
    }

    public function fromActivity(Activity $from, &$to): void
    {
        if ($this->valueHasChanged($from->getNsfw(), $to->getNsfw())) {
            $to->setNsfw($from->getNsfw());
        }

        if ($this->valueHasChanged($from->getNsfwLock(), $to->getNsfwLock())) {
            $to->setNsfwLock($from->getNsfwLock());
        }
    }
}
