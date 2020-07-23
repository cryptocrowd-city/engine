<?php
/**
 * Minds Permaweb Provider
 */

namespace Minds\Core\Permaweb;

use Minds\Core\Di\Provider;
use Minds\Core\Permaweb\Manager;

class PermawebProvider extends Provider
{
    public function register()
    {
        $this->di->bind('Permaweb\Manager', function ($di) {
            return new Manager();
        }, ['useFactory'=>true]);
    }
}
