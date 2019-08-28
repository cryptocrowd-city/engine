<?php

namespace Minds\Core\Feeds\Delegates;

use Minds\Core\Entities\Propogator\Properties;
use Minds\Entities\Activity;

class PropogateProperties extends Properties
{
    public function toActivity($from, Activity &$to): void
    {
        if ($this->valueHasChanged($from->getModeratorGuid(), $to->getModeratorGuid()))
            $to->setModeratorGuid($from->getModeratorGuid());

        if ($this->valueHasChanged($from->getTimeModerated(), $to->getModeratorGuid()))
            $to->setTimeModerated($from->getTimeModerated());
    }

    public function fromActivity(Activity $from, &$to): void
    {
        if ($this->valueHasChanged($from->getModeratorGuid(), $to->getModeratorGuid()))
            $to->setModeratorGuid($from->getModeratorGuid());

        if ($this->valueHasChanged($from->getTimeModerated(), $to->getModeratorGuid()))
            $to->setTimeModerated($from->getTimeModerated());
    }
}
