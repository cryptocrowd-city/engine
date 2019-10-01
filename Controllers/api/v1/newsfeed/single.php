<?php
/**
 * Minds Newsfeed Single refactor API
 *
 * @version 1
 * @author Brian Hatchet
 */

namespace Minds\Controllers\api\v1;

use Minds\Api\Factory;
use Minds\Core;
use Minds\Core\Security;
use Minds\Entities;
use Minds\Entities\Activity;
use Minds\Helpers;
use Minds\Entities\Factory as EntitiesFactory;
use Minds\Helpers\Counters;
use Minds\Interfaces;
use Minds\Interfaces\Flaggable;
use Minds\Core\Di\Di;
use Minds\Core\Entities\Actions\Save;

class newsfeed implements Interfaces\Api
{
    /**
     * Returns the newsfeed
     * @param array $pages
     *
     * API:: /v1/newsfeed/single/:guid
     */
    public function get($pages)
    {
        $activity = new Activity($pages[1]);

        if (!Security\ACL::_()->read($activity)) {
            return Factory::response([
                        'status' => 'error',
                        'message' => 'You do not have permission to view this post'
                    ]);
        }

        if (!$activity->guid || Helpers\Flags::shouldFail($activity)) {
            return Factory::response(['status' => 'error']);
        }

        $response = [
            'activity' =>  $activity->export()
        ];

        return Factory::response($response);
    }

    public function post($pages)
    {
    }

    public function put($pages)
    {
    }

    public function delete($pages)
    {
    }
}
