<?php
/**
 * @author: eiennohi.
 */

namespace Minds\Core\DeferredOps;

use Minds\Core\Di\Provider as DiProvider;

class Provider extends DiProvider
{
    public function register()
    {
        $this->di->bind('DeferredOps\Manager', function ($di) {
            return new Manager();
        }, ['useFactory' => true]);

        $this->di->bind('DeferredOps\Repository', function ($di) {
            return new Repository();
        }, ['useFactory' => true]);
    }
}
