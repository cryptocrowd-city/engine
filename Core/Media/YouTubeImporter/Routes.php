<?php
/**
 * Routes
 * @author edgebal
 */

namespace Minds\Core\Media\YoutubeImporter;

use Minds\Core\Di\Ref;
use Minds\Core\Router\Middleware\LoggedInMiddleware;
use Minds\Core\Router\ModuleRoutes;
use Minds\Core\Router\Route;

class Routes extends ModuleRoutes
{
    /**
     * Registers all module routes
     */
    public function register(): void
    {
        $this->route
            ->withPrefix('api/v3/media/youtube-importer')
            ->withMiddleware([
                LoggedInMiddleware::class,
            ])
            ->do(function (Route $route) {
                // Requests OAuth token
                $route->get(
                    'oauth',
                    Ref::_('Media\YouTubeImporter\Controller', 'getToken')
                );

                // Requests OAuth token
                $route->get(
                    'oauth/redirect',
                    Ref::_('Media\YouTubeImporter\Controller', 'receiveToken')
                );

                // returns list of videos
                $route->get(
                    'videos',
                    Ref::_('Media\YouTubeImporter\Controller', 'getVideos')
                );

                // imports a video
                $route->post(
                    'videos/import',
                    Ref::_('Media\YouTubeImporter\Controller', 'import')
                );
            });
    }
}
