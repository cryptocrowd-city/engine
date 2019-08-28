<?php
/**
 *
 */
namespace Minds\Controllers\api\v2\payments\stripe;

use Minds\Api\Factory;
use Minds\Common\Cookie;
use Minds\Core\Di\Di;
use Minds\Core\Config;
use Minds\Core\Session;
use Minds\Interfaces;
use Minds\Core\Payments\Stripe;

class connect implements Interfaces\Api
{
    public function get($pages)
    {
        $user = Session::getLoggedInUser();

        $connectManager = new Stripe\Connect\Manager();

        switch ($pages[0]) {
            case 'bank':
                break;
            default:
                $account = $connectManager->getByUser($user);
                return Factory::response([
                    'account' => $account->export(),
                ]);
        }

        return Factory::response([
        ]);
    }

    public function post($pages)
    {
        $user = Session::getLoggedInUser();
        $connectManager = new Stripe\Connect\Manager();

        switch ($pages[0]) {
            case 'bank':
                $account = $connectManager->getByUser($user);
                if (!$account) {
                    return Factory::response([
                        'status' => 'error',
                        'message' => 'You must have a USD account to add a bank account',
                    ]);
                }

                $account->setAccountNumber($_POST['accountNumber'])
                    ->setCountry($_POST['country'])
                    ->setRoutingNumber($_POST['routingNumber']);
                $connectManager->addBankAccount($account);
            break;
        }
        return Factory::response([]);
    }

    public function put($pages)
    {
        return Factory::response([]);
    }

    public function delete($pages)
    {
        $user = Session::getLoggedInUser();

        $account = new Stripe\Connect\Account();
        $account->setUserGuid($user->getGuid())
            ->setUser($user)
            ->setId($user->getMerchant()['id']);

        $connectManager = new Stripe\Connect\Manager();
        $connectManager->delete($account);
        return Factory::response([]);
    }
}
