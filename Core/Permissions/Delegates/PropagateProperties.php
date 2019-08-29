<?php

namespace Minds\Core\Permissions\Delegates;

use Minds\Core\Entities\Propagator\Properties;
use Minds\Entities\Activity;

class PropagateProperties extends Properties
{
    public function toActivity($from, Activity $to): Activity
    {
        if ($this->valueHasChanged($from->getAllowComments(), $to->getAllowComments())) {
            $to->setAllowComments($from->getAllowComments());
        }

        return $to;
    }

    public function fromActivity(Activity $from, $to)
    {
        if ($this->valueHasChanged($from->getAllowComments(), $to->getAllowComments())) {
            $to->setAllowComments($from->getAllowComments());
        }

        return $to;
    }
}
