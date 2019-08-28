<?php

namespace Minds\Core\Media\Delegates;

use Minds\Core\Entities\Propogator\Properties;
use Minds\Entities\Activity;

class PropogateProperties extends Properties
{
    protected $actsOnType = 'object';
    protected $actsOnSubtype = ['image', 'video'];

    public function toActivity($from, Activity &$to): void
    {
        if ($this->valueHasChanged($from->title, $to->getMessage()))
            $to->setMessage($from->title);

        $fromData = $from->getActivityParameters();
        $toData = $to->getCustom();
        if ($this->valueHasChanged($fromData[1], $toData[1]))
            $to->setCustom($fromData[0], $fromData[1]);
    }

    public function fromActivity(Activity $from, &$to): void
    {
        // TODO: Implement fromActivity() method.
    }
}
