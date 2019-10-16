<?php

namespace Minds\Core\Groups\Delegates;

use Minds\Entities\Activity;

/**
 * Class PropagateRejection
 *
 * Propagates activity deletion from the group feed after rejection.
 * @package Minds\Core\Groups\Delegates
 * @author Ben Hayward
 */
class PropagateRejection
{
    /**
     * Deletes an activity associated with a rejected post.
     *
     * @param string $guid - activity guid.
     * @return boolean - whether deletion was successful
     */
    public function deleteActivity($guid): bool
    {
        $activity = new Activity($guid);
        return $activity->delete();
    }
}
