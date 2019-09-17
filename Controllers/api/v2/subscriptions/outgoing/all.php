<?php

namespace Minds\Controllers\api\v2\subscriptions\outgoing;

use Minds\Api\Factory;
use Minds\Core\Di\Di;
use Minds\Core\Session;
use Minds\Interfaces;

/**
 * Outgoing subscritions
 */
class all implements Interfaces\Api
{
    public function get($pages)
    {
        // Todo

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
