<?php
/**
 * @author: eiennohi.
 */

namespace Minds\Core\Media\YouTubeImporter;

use Zend\Diactoros\Response\JsonResponse;
use Zend\Diactoros\ServerRequest;

class Controller
{
    public function __construct()
    {

    }

    public function getVideos(ServerRequest $request): JsonResponse
    {
        $queryParams = $request->getQueryParams();

        return new JsonResponse([
            'status' => 'success',
        ]);
    }

    public function getImportableVideos(ServerRequest $request): JsonResponse
    {

        return new JsonResponse([
            'status' => 'success',
        ]);
    }
}
