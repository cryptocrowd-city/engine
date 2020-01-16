<?php

/**
 * Minds Features Provider
 *
 * @author emi
 */

namespace Minds\Core\Features;

use Minds\Core\Di\Provider as DiProvider;

class Provider extends DiProvider
{
    public function register()
    {
        $this->di->bind('Features\Manager', function ($di) {
            return new Manager();
        }, [ 'useFactory'=> true ]);

        $this->di->bind('Features\Canary', function ($di) {
            return new Canary();
        }, [ 'useFactory'=> true ]);

        $this->di->bind('Features', function ($di) {
            return $di->get('Features\Manager');
        }, [ 'useFactory'=> true ]);
    }
}
