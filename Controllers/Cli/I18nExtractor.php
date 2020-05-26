<?php

namespace Minds\Controllers\Cli;

use Minds\Core;
use Minds\Core\Analytics\EntityCentric\Manager;
use Minds\Cli;
use Minds\Interfaces;
use Minds\Exceptions;
use Minds\Entities;
use Symfony\Component\Finder\Iterator\RecursiveDirectoryIterator;
use Symfony\Component\Translation\Catalogue\TargetOperation;
use Symfony\Component\Translation\Extractor\PhpExtractor;
use Symfony\Component\Translation\MessageCatalogue;

class I18nExtractor extends Cli\Controller implements Interfaces\CliControllerInterface
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
        //        $locale = $this->getOpt('locale');
        //
        //        if(!$locale){
        //            $this->out('You must specify a locale with --locale');
        //        }

        $locale = 'es';

        /** @var Core\I18n\Translator $translator */
        $translator = Core\Di\Di::_()->get('Translator');
        $translator->setLocale('es');

        $files = $this->getFiles();

        /** @var MessageCatalogue $sourceCatalogue */
        $sourceCatalogue = $translator->getTranslator()->getCatalogue();

        $this->extract($files, $sourceCatalogue);
    }

    private function getFiles(): array
    {
        $files = [];
        $directory = new \RecursiveDirectoryIterator(getcwd());
        $iterator = new \RecursiveIteratorIterator($directory);

        /** @var \SplFileInfo $file */
        foreach ($iterator as $file) {
            // ignore vendor folder
            if (strpos($file->getPathname(), '/engine/vendor')) {
                continue;
            }

            // ignore classes folder
            if (strpos($file->getPathname(), '/engine/classes')) {
                continue;
            }

            // ignore relative folders
            if (strpos($file->getPathname(), '/..') || strpos($file->getPathname(), '/.')) {
                continue;
            }

            // only look for php, tpl and md.tpl files
            if (!strpos($file->getFilename(), '.php') && !strpos($file->getFilename(), '.tpl')) {
                continue;
            }

            $files[] = $file->getPathname();
        }

        return $files;
    }

    private function extract(array $files, MessageCatalogue $sourceCatalogue)
    {
        $extractor = new PhpExtractor();
        //        /** @var MessageCatalogue $catalogue */
        //        $catalogue = $translator->getTranslator()->getCatalogue();
        $targetCatalogue = new MessageCatalogue($sourceCatalogue->getLocale());

        $extractor->extract($files, $targetCatalogue);

        $operation = new TargetOperation($sourceCatalogue, $targetCatalogue);

        var_dump(array_values($operation->getNewMessages('messages')));
        die();
    }
}
