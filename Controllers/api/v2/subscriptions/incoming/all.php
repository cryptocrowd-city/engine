<?php

namespace Minds\Controllers\api\v2\subscriptions\incoming;

use Minds\Api\Factory;
use Minds\Core\Di\Di;
use Minds\Core\Session;
use Minds\Interfaces;

/**
 * Incoming subscritions
 */
class all implements Interfaces\Api
{
    public function get($pages)
    {
        // Return a list of subscription requests
        $manager = Di::_()->get('Subscriptions\Requests\Manager');

        $requests = $manager->getIncomingList(Session::getLoggedInUserGuid(), []);

        return Factory::response([
            'requests' => Factory::exportable($requests),
            'next' => $requests->getPagingToken(),
        ]);
    }

    public function post($pages)
    {
        // Void
        return Factory::response([]);
    }

    public function put($pages)
    {
        // Void
        return Factory::response([]);
    }

    public function delete($pages)
    {
        // Void
        return Factory::response([]);
    }
}
