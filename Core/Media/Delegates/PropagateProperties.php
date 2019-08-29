<?php

namespace Minds\Core\Media\Delegates;

use Minds\Core\Entities\Propagator\Properties;
use Minds\Entities\Activity;

class PropagateProperties extends Properties
{
    protected $actsOnType = 'object';
    protected $actsOnSubtype = ['image', 'video'];

    public function toActivity($from, Activity $to): Activity
    {
        if ($this->valueHasChanged($from->title, $to->getMessage())) {
            $to->setMessage($from->title);
        }

        $fromData = $from->getActivityParameters();
        $toData = $to->getCustom();
        if ((!isset($toData[1])) || (isset($toData[1]) && $this->valueHasChanged($fromData[1], $toData[1]))) {
            $to->setCustom($fromData[0], $fromData[1]);
        }

        return $to;
    }

    public function fromActivity(Activity $from, $to)
    {
        return $to;
    }
}
