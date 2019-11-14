<?php
/**
 * authorize
 * @author edgebal
 */

namespace Minds\Controllers\api\v2\sso;

use Exception;
use Minds\Api\Factory;
use Minds\Core\Di\Di;
use Minds\Core\Session;
use Minds\Core\SSO\Manager;
use Minds\Entities\User;
use Minds\Interfaces;
use Zend\Diactoros\ServerRequest;

class authorize implements Interfaces\Api, Interfaces\ApiIgnorePam
{
    /** @var ServerRequest */
    public $request;

    /**
     * Equivalent to HTTP GET method
     * @param array $pages
     * @return mixed|null
     */
    public function get($pages)
    {
        return Factory::response([]);
    }

    /**
     * Equivalent to HTTP POST method
     * @param array $pages
     * @return mixed|null
     * @throws Exception
     */
    public function post($pages)
    {
        $host = $this->request->getServerParams()['HTTP_HOST'] ?? '';

        if (!$host) {
            return Factory::response([
                'status' => 'error',
                'message' => 'No HTTP Host header',
            ]);
        }

        /** @var Manager $sso */
        $sso = Di::_()->get('SSO');
        $sso
            ->setDomain($host);

        if (!$sso->isAllowed()) {
            return Factory::response([
                'status' => 'error',
                'message' => 'Domain not allowed',
            ]);
        }

        // TODO: Use headers
        $jwt = $_POST['token'];

        $success = $sso
            ->authorize($jwt);

        if (!$success) {
            return Factory::response([
                'status' => 'error',
                'message' => 'Invalid token',
            ]);
        }

        /** @var User $currentUser */
        $currentUser = Session::getLoggedinUser();

        return Factory::response([
            'user' => $currentUser ? $currentUser->export() : null,
        ]);
    }

    /**
     * Equivalent to HTTP PUT method
     * @param array $pages
     * @return mixed|null
     */
    public function put($pages)
    {
        return Factory::response([]);
    }

    /**
     * Equivalent to HTTP DELETE method
     * @param array $pages
     * @return mixed|null
     */
    public function delete($pages)
    {
        return Factory::response([]);
    }
}
