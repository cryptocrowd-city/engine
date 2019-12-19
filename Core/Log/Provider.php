<?php
/**
 * Provider
 *
 * @author edgebal
 */

namespace Minds\Core\Log;

use Minds\Core\Config;
use Minds\Core\Di\Di;
use Minds\Core\Di\Provider as DiProvider;

class Provider extends DiProvider
{
    public function register()
    {
        $this->di->bind('Logger', function ($di) {
            /** @var Di $di */

            /** @var Config|false $config */
            $config = $di->get('Config');

            $options = [
                'isProduction' => $config ? $config->get('development_mode') : true,
                'devToolsLogger' => $config ? $config->get('dev_tools_logger') : '',
            ];

            return new Logger('Minds', $options);
        }, [ 'useFactory' => false ]);
    }
}
