<?php
/**
 * RouterProvider
 * @author edgebal
 */

namespace Minds\Core\Router;

use Minds\Core\Di\Provider;

class RouterProvider extends Provider
{
    public function register()
    {
        $this->di->bind('Router', function ($di) {
            return new Dispatcher();
        }, ['useFactory' => true]);
    }
}
