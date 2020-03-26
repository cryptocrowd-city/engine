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
                // returns list of already transferred videos
                $route->get(
                    'videos',
                    Ref::_('Media\YouTubeImporter\Controller', 'getVideos')
                );
                // returns a list of available videos from YouTube, and videos that are in process or queued
                $route->get(
                    'videos/import',
                    Ref::_('Media\YouTubeImporter\Controller', 'getImportableVideos')
                );
            });
    }
}
