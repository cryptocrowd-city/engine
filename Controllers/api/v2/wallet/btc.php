<?php
/**
 * BTC Wallet Controller
 *
 * @version 1
 * @author Mark Harding
 */
namespace Minds\Controllers\api\v2\wallet;

use Minds\Core;
use Minds\Core\Entities\Actions;
use Minds\Helpers;
use Minds\Interfaces;
use Minds\Api\Factory;
use Minds\Core\Payments;
use Minds\Entities;

class btc implements Interfaces\Api
{
    /**
     * Returns merchant information
     * @param array $pages
     *
     * API:: /v1/merchant/:slug
     */
    public function get($pages)
    {
        Factory::isLoggedIn();

        $response = [];

        switch ($pages[0]) {
            case "address":
                $response['address'] = Core\Session::getLoggedInUser()->getBtcAddress(); 
                break;
        }

        return Factory::response($response);
    }

    public function post($pages)
    {
        Factory::isLoggedIn();

        $response = [];

        $user = Core\Session::getLoggedInUser();
        $save = new Actions\Save();
            
        switch ($pages[0]) {
            case "address":
                $user->setBtcAddress($_POST['address']);
                $save->setEntity($user)
                    ->save();
                break;
        }

        return Factory::response($response);
    }

    public function put($pages)
    {
        return Factory::response(array());
    }

    public function delete($pages)
    {
        return Factory::response(array());
    }

}
