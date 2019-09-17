<?php

namespace Minds\Controllers\api\v2\subscriptions;

use Minds\Api\Factory;
use Minds\Core\Di\Di;
use Minds\Core\Session;
use Minds\Entities\Factory as EntitiesFactory;
use Minds\Interfaces;

/**
 * Outgoing subscritions
 */
class outgoing implements Interfaces\Api
{
    public function get($pages)
    {
        // Return a single request
        $manager = Di::_()->get('Subscriptions\Requests\Manager');
        
        // Construct URN on the fly
        $publisherGuid = $pages[0];
        $urn = "urn:subscription-request:" . implode('-', [ $publisherGuid, Session::getLoggedInUserGuid() ]);
        
        $request = $manager->get($urn);

        if (!$request || $request->getSubscriberGuid() != Session::getLoggedInUserGuid()) {
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
        // Make a subscription request
        $manager = Di::_()->get('Subscriptions\Requests\Manager');

        $request = new SubscriptionRequest();
        $request->setPublisherGuid($pages[0])
            ->setSubscriberGuid(Session::getLoggedInGuid());

        try {
            $manager->add($request);
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
        // Delete a subscription request
        return Factory::response([]);
    }
}
