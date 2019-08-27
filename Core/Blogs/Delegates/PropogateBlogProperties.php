<?php

namespace Minds\Core\Blogs\Delegates;

use Minds\Core\Entities\Propogator\Properties;
use Minds\Entities\Activity;

class PropogateBlogProperties extends Properties
{
    protected $actsOnSubtype = 'blog';

    public function toActivity($from, Activity &$to): void
    {
        if ($this->valueHasChanged($from->getTitle(), $to->get('title'))) {
            $to->set('title', $from->getTitle());
        }

        $blurb = strip_tags($from->getBody());
        if ($this->valueHasChanged($blurb, $to->get('blurb'))) {
            $to->set('blurb', $blurb);
        }

        if ($this->valueHasChanged($from->getURL(), $to->getURL())) {
            $to->setURL($from->getURL());
        }

        if ($this->valueHasChanged($from->getIconUrl(), $to->get('thumbnail_src'))) {
            $to->set('thumbnail_src', $from->getIconUrl());
        }
    }

    public function fromActivity(Activity $from, &$to): void
    {
        // TODO: Implement fromActivity() method.
    }
}
