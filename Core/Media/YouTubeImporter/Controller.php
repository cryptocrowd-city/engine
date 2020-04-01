<?php
/**
 * @author: eiennohi.
 */

namespace Minds\Core\Media\YouTubeImporter;

use Minds\Api\Exportable;
use Minds\Core\Config\Config;
use Minds\Core\Di\Di;
use Minds\Core\Session;
use Minds\Entities\User;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Diactoros\ServerRequest;

class Controller
{
    /** @var Manager */
    protected $manager;

    /** @var Config */
    protected $config;

    public function __construct($manager = null, $config = null)
    {
        $this->manager = $manager ?: Di::_()->get('Media\YouTubeImporter\Manager');
        $this->config = $config ?: Di::_()->get('Config');
    }

    public function getToken(ServerRequest $request): JsonResponse
    {
        return new JsonResponse([
            'status' => 'success',
            'url' => $this->manager->connect(),
        ]);
    }

    public function receiveToken(ServerRequest $request): JsonResponse
    {
        $token = null;
        $code = $request->getQueryParams()['code'];

        if (!isset($code)) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'Missing code',
            ]);
        }

        /** @var User $user */
        $user = Session::getLoggedinUser();

        $this->manager->receiveToken($user, $code);

        // redirect back to the URL
        // TODO this should redirect to an URL with the youtube importer opened
        header('Location: ' . filter_var($this->config->get('site_url'), FILTER_SANITIZE_URL));
        exit;
    }

    public function getVideos(ServerRequest $request): JsonResponse
    {
        $queryParams = $request->getQueryParams();

        if (!isset($queryParams['channelId'])) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'You must provide a channelId',
            ]);
        }

        $channelId = $queryParams['channelId'];

        $status = $queryParams['status'] ?? 'all';

        /** @var User $user */
        $user = Session::getLoggedinUser();

//        try {
        $videos = $this->manager->getVideos($user, $channelId, $status);

        return new JsonResponse([
                'status' => 'success',
                'videos' => Exportable::_($videos),
            ]);
//        } catch (\Exception $e) {
//            return new JsonResponse([
//                'status' => 'error',
//                'message' => $e->getMessage(),
//            ]);
//        }
    }

    public function import(ServerRequest $request): JsonResponse
    {
        $params = $request->getParsedBody();

        /** @var User $user */
        $user = Session::getLoggedinUser();

        if (!isset($params['channelId'])) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'You must provide a channelId',
            ]);
        }

        $channelId = $params['channelId'];

        // if the channel does not belong to the User
        if (count(array_filter($user->getYouTubeChannels(), function ($value) use ($channelId) {
            return $value['id'] === $channelId;
        })) === 0) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'channelId is not registered to this user',
            ]);
        }

        if (!isset($params['videoId'])) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'You must provide a videoId',
            ]);
        }

        $videoId = $params['videoId'];

        $this->manager->import($user, $channelId, $videoId);

        return new JsonResponse([
            'status' => 'success',
        ]);
    }
}
