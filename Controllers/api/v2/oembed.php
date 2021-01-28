<?php

namespace Minds\Controllers\api\v2;

use Minds\Api\Factory;
use Minds\Interfaces;
use Minds\Core\Di\Di;

/**
 * oEmbed endpoint for returning summary objects of video and images.
 *
 * Specifications: https://oembed.com/
 */
class oembed implements Interfaces\Api
{
    /**
     * Equivalent to HTTP GET method
     * @param  array $pages
     * @return mixed|null
     * @throws \Exception
     */
    public function get($pages)
    {
        $params = [
            'url' => filter_var($_GET['url'] ?? false, FILTER_VALIDATE_URL),
            'format' => filter_var($_GET['format'] ?? 'json', FILTER_SANITIZE_STRING),
            'maxwidth' => (int) filter_var($_GET['maxwidth'] ?? '', FILTER_SANITIZE_STRING),
            'maxheight' => (int) filter_var($_GET['maxheight'] ?? '', FILTER_SANITIZE_STRING),
        ];

        if ($params['format'] !== 'json') {
            return Factory::response([
                'status' => 501,
                'message' => 'Unsupported format, only the default format, "json" is currently supported.',
            ]);
        }

        if (!$params['url']) {
            return Factory::response([
                'status' => 400,
                'message' => 'This URL appears to be invalid, please ensure that the url is properly encoded.',
            ]);
        }

        // Split url at newsfeed
        $queryString = explode('newsfeed/', $params['url'])[1];
        $guid = explode('?', $queryString)[0];

        if (!filter_var($guid ?? false, FILTER_VALIDATE_INT)) {
            return FactoryA::response([
                'status' => 400,
                'message' => 'Invalid GUID format.',
            ]);
        }

        $this->entitiesBuilder = Di::_()->get('EntitiesBuilder');
        $entity = $this->entitiesBuilder->single($guid);

        // TODO: Paywall check
        if (!$entity) {
            return Factory::response([
                'status' => 404,
                'message' => 'Entity not found.'
            ]);
        }

        // image or video
        if ($entity->type !== 'object') {
            return Factory::response([
                'status' => 501,
                'message' => 'Only image and video links are supported.',
            ]);
        }

        $version = 1;

        $height = $entity->height;
        $width = $entity->width;

        if (!$height || !$width) {
            $height = 720;
            $width = 1280;
        }

        if ($params['maxheight'] && $height > $params['maxheight']) {
            $height = $params['maxheight'];
        }

        if ($params['maxwidth'] && $width > $params['width']) {
            $width = $params['maxwidth'];
        }

        switch ($entity->subtype) {
            case 'video':
                $type = 'video';

                return Factory::response([
                    'status' => 200,
                    'html' => "<iframe src=\"https://www.minds.com/embed/$entity->guid\"></iframe>",
                    'height' => $height,
                    'width' => $width,
                ]);
                break;
            case 'image':
                $type = 'photo';
                $exportedEntity = $entity->export();
                $url = $exportedEntity['thumbnail_src'] ?: $$exportedEntity['thumbnail'] ?: '';

                if (!$url) {
                    return Factory::response([
                        'status' => 500,
                        'message' => 'An unknown error hs occurred.'
                    ]);
                    break;
                }

                return Factory::response([
                    'status' => 200,
                    'type' => $type,
                    'version' => $version,
                    'url' => $url,
                    'width' => $width,
                    'height' => $height,
                ]);
                break;
            default:
                return Factory::response([
                    'status' => 501,
                    'message' => 'Only image and video links are supported.',
                ]);
                break;
        }
    }

    /**
     * Equivalent to HTTP POST method
     * @param  array $pages
     * @return mixed|null
     * @throws \Exception
     */
    public function post($pages)
    {
        return Factory::response([]);
    }

    /**
     * Equivalent to HTTP PUT method
     * @param  array $pages
     * @return mixed|null
     */
    public function put($pages)
    {
        return Factory::response([]);
    }

    /**
     * Equivalent to HTTP DELETE method
     * @param  array $pages
     * @return mixed|null
     */
    public function delete($pages)
    {
        return Factory::response([]);
    }
}
