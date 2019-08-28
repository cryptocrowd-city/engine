<?php

namespace Minds\Core\Permissions;

use Minds\Core\Entities\Propogator\Properties;
use Minds\Entities\Activity;

class PropogateProperties extends Properties
{
    public function toActivity($from, Activity &$to): void
    {
        if ($this->valueHasChanged($from->getAllowComments(), $to->getAllowComments()))
            $to->setAllowComments($from->getAllowComments());
    }

    public function fromActivity(Activity $from, &$to): void
    {
        if ($this->valueHasChanged($from->getAllowComments(), $to->getAllowComments()))
            $to->setAllowComments($from->getAllowComments());
    }
}
