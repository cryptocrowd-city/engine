<?php

namespace Minds\Core\Permissions\Delegates;

use Minds\Core\Entities\Propagator\Properties;
use Minds\Entities\Activity;

class PropagateProperties extends Properties
{
    public function toActivity($from, Activity &$to): void
    {
        if ($this->valueHasChanged($from->getAllowComments(), $to->getAllowComments())) {
            $to->setAllowComments($from->getAllowComments());
        }
    }

    public function fromActivity(Activity $from, &$to): void
    {
        if ($this->valueHasChanged($from->getAllowComments(), $to->getAllowComments())) {
            $to->setAllowComments($from->getAllowComments());
        }
    }
}
