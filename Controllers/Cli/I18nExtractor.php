<?php

namespace Minds\Controllers\Cli;

use Minds\Core;
use Minds\Core\Analytics\EntityCentric\Manager;
use Minds\Cli;
use Minds\Interfaces;
use Minds\Exceptions;
use Minds\Entities;
use Symfony\Component\Finder\Iterator\RecursiveDirectoryIterator;
use Symfony\Component\Translation\Extractor\PhpExtractor;
use Symfony\Component\Translation\MessageCatalogue;

class Extractor extends Cli\Controller implements Interfaces\CliControllerInterface
{
    public function __construct()
    {
    }

    public function help($command = null)
    {
        $this->out('TBD');
    }

    public function exec()
    {
        $extractor = new PhpExtractor();
        $files = [
            getcwd() . '/Controllers/api/v1/forgotpassword.php',
        ];
        //        $catalogue = new MessageCatalogue('fr');
        //        $extractor->extract($files,$catalogue);

        $iterator = new \RecursiveDirectoryIterator(getcwd());

        /** @var \SplFileInfo $file */
        foreach (new \RecursiveIteratorIterator($iterator) as $file) {
            // ignore vendor folder
            if (strpos($file->getPathname(), '/engine/vendor')) {
                continue;
            }

            // ignore relative folders
            if (strpos($file->getPathname(), '/..') || strpos($file->getPathname(), '/.')) {
                continue;
            }

            // only look for php and tpl files
            if (!strpos($file->getFilename(), '.php') && !strpos($file->getFilename(), '.tpl')) {
                continue;
            }

            var_dump($file->getFilename());
        }

        //        var_dump($catalogue->all());
    }
}
