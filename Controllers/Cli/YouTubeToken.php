<?php
/**
 * @author: eiennohi.
 */

namespace Minds\Controllers\Cli;

use Minds\Core;
use Minds\Cli;
use Minds\Interfaces;
use Minds\Exceptions;
use Minds\Exceptions\ProvisionException;

class YouTubeToken extends Cli\Controller implements Interfaces\CliControllerInterface
{
    public function __construct()
    {
        define('__MINDS_INSTALLING__', true);
    }

    public function help($command = null)
    {
        $this->out('TBD');
    }

    public function exec()
    {
        /** @var Core\Media\YouTubeImporter\Manager $manager */
        $manager = Core\Di\Di::_()->get('Media\YouTubeImporter\Manager');

        $this->out('Open the following url: ');
        $this->out($manager->connect(true));
    }
}
