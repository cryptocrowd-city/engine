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
        // Return a single request
        $manager = Di::_()->get('Subscriptions\Requests\Manager');
        
        // Construct URN on the fly
        $subscriberGuid = $pages[0];
        $urn = "urn:subscription-request:" . implode('-', [ Session::getLoggedInUserGuid(), $subscriberGuid ]);
        
        $request = $manager->get($urn);

        if (!$request || $request->getPublisherGuid() != Session::getLoggedInUserGuid()) {
            return Factory::response([
                'status' => 'error',
                'message' => 'Not found',
            ]);
        }

        return Factory::response([
            'request' => $request->export(),
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
        $manager = Di::_()->get('Subscriptions\Requests\Manager');
        
        // Construct URN on the fly
        $subscriberGuid = $pages[0];
        $urn = "urn:subscription-request:" . implode('-', [ Session::getLoggedInUserGuid(), $subscriberGuid ]);
        
        $request = $manager->get($urn);
        
        if (!$request || $request->getPublisherGuid() != Session::getLoggedInUserGuid()) {
            return Factory::response([
                'status' => 'error',
                'message' => 'Not found',
            ]);
        }

        try {
            switch ($pages[1]) {
                case "accept":
                    $manager->accept($request);
                    break;
                case "decline":
                    $manager->decline($request);
                    break;
            }
        } catch (\Exception $e) {
            return Factory::response([
                'status' => 'error',
                'message' => $e->getMessage(),
            ]);
        }

        return Factory::response([]);
    }

    public function delete($pages)
    {
        // Void
        return Factory::response([]);
    }
}
