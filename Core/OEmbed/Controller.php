<?php
/**
 * Minds OEmbed Controller
 *
 * @version 1
 */

namespace Minds\Core\OEmbed;

use Zend\Diactoros\Response\JsonResponse;
use Zend\Diactoros\ServerRequest;
use Minds\Core\Di\Di;

const OEMBED_VERSION = 1;

class Controller
{
    /** @var EntitiesBuilder */
    protected $entitiesBuilder;

    public function __construct(
        $entitiesBuilder = null
    ) {
        $this->entitiesBuilder = $entitiesBuilder ?: Di::_()->get('EntitiesBuilder');
    }

    /**
     * @param ServerRequest $request
     * @return mixed|null
     */
    public function getOEmbed(ServerRequest $request): JsonResponse
    {
        $queryParams = $request->getQueryParams();

        $params = [
            'url' => filter_var($queryParams['url'] ?? false, FILTER_VALIDATE_URL),
            'format' => filter_var($queryParams['format'] ?? 'json', FILTER_SANITIZE_STRING),
            'maxwidth' => (int) filter_var($queryParams['maxwidth'] ?? '', FILTER_SANITIZE_STRING),
            'maxheight' => (int) filter_var($queryParams['maxheight'] ?? '', FILTER_SANITIZE_STRING),
        ];

        if ($params['format'] !== 'json') {
            return new JsonResponse([
                'status' => 501,
                'message' => 'Unsupported format, only the default format, "json" is currently supported.',
            ]);
        }

        if (!$params['url']) {
            return new JsonResponse([
                'status' => 400,
                'message' => 'This URL appears to be invalid, please ensure that the url is properly encoded.',
            ]);
        }

        $guid = $this->trimGuid($params['url']);
        
        if (!filter_var($guid ?? false, FILTER_VALIDATE_INT)) {
            return new JsonResponse([
                'status' => 400,
                'message' => 'Invalid GUID format.',
            ]);
        }

        $entity = $this->entitiesBuilder->single($guid);

        // TODO: Paywall check
        if (!$entity) {
            return new JsonResponse([
                'status' => 404,
                'message' => 'Entity not found.'
            ]);
        }

        // image or video
        if ($entity->type !== 'object') {
            return new JsonResponse([
                'status' => 501,
                'message' => 'Only image and video links are supported.',
            ]);
        }

        $version = $this->OEMBED_VERSION;

        $dimensions = $this->getRestrictedDimensions($entity, $params['maxheight'], $params['maxwidth']);

        $height = $dimensions['height'];
        $width = $dimensions['width'];

        switch ($entity->subtype) {
            case 'video':
                $type = 'video';

                return new JsonResponse([
                    'status' => 200,
                    'html' => "<iframe src=\"https://www.minds.com/embed/$entity->guid\"></iframe>",
                    'height' => $height,
                    'width' => $width,
                    'type' => $type,
                    'version' => $version,
                ]);
                break;
            case 'image':
                $type = 'photo';
                $exportedEntity = $entity->export();
                $url = $exportedEntity['thumbnail_src'] ?: $$exportedEntity['thumbnail'] ?: '';

                if (!$url) {
                    return new JsonResponse([
                        'status' => 500,
                        'message' => 'An unknown error hs occurred.'
                    ]);
                    break;
                }

                return new JsonResponse([
                    'status' => 200,
                    'type' => $type,
                    'version' => $version,
                    'url' => $url,
                    'width' => $width,
                    'height' => $height,
                ]);
                break;
            default:
                return new JsonResponse([
                    'status' => 501,
                    'message' => 'Only image and video links are supported.',
                ]);
                break;
        }
    }

    /**
     * Trims GUID from a URL.
     * @param string $url - url to be trimmed.
     * @return string - guid.
     */
    private function trimGuid(string $url): string
    {
        $queryString = explode('newsfeed/', $url)[1];
        return explode('?', $queryString)[0];
    }
    
    /**
     * Gets dimensions restricted by max height and width from an entity.
     * @param $entity - entity to be presented.
     * @param $maxHeight - the maximum height.
     * @param $maxWidth - the maximum width.
     * @return array height and width parameters in an array.
     */
    private function getRestrictedDimensions($entity, $maxHeight, $maxWidth): array
    {
        $height = $entity->height;
        $width = $entity->width;

        if (!$height || !$width) {
            $height = 720;
            $width = 1280;
        }

        if ($maxHeight && $height > $maxHeight) {
            $height = $maxHeight;
        }

        if ($maxWidth && $width > $maxWidth) {
            $width = $maxWidth;
        }

        return [
            'width' => $width,
            'height' => $height
        ];
    }
}
