<?php

namespace Minds\Controllers\api\v2\subscriptions;

use Minds\Api\Factory;
use Minds\Core\Di\Di;
use Minds\Core\Session;
use Minds\Interfaces;

/**
 * Incoming subscritions
 */
class incoming implements Interfaces\Api
{
    public function get($pages)
    {
        // Return a list of subscriptions

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
        // Accept / Deny
        return Factory::response([]);
    }

    public function delete($pages)
    {
        // Void
        return Factory::response([]);
    }
}
