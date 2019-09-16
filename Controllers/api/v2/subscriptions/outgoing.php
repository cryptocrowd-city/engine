<?php

namespace Minds\Controllers\api\v2\subscriptions;

use Minds\Api\Factory;
use Minds\Core\Di\Di;
use Minds\Core\Session;
use Minds\Interfaces;

/**
 * Outgoing subscritions
 */
class outgoing implements Interfaces\Api
{
    public function get($pages)
    {
        // Return a list of subscriptions we made

        return Factory::response([

        ]);
    }

    public function post($pages)
    {
        // Void
        return Factory::response([]);
    }

    public function put($pages)
    {
        // Make a subscription request
        return Factory::response([]);
    }

    public function delete($pages)
    {
        // Delete a subscription request
        return Factory::response([]);
    }
}
