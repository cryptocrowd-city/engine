<?php
/**
 * @author: eiennohi.
 */

namespace Minds\Core\I18n;

use Minds\Core\Config\Config;
use Minds\Core\Di\Di;
use Minds\Core\I18n\Loaders\XliffFileLoader;
use Minds\Core\Log\Logger;
use Symfony\Component\Translation\Translator as SymfonyTranslator;

class Translator
{
    /** @var SymfonyTranslator */
    protected $translator;

    /** @var Config */
    protected $config;

    /** @var Logger */
    protected $logger;

    function __construct(
        $config = null,
        $translator = null,
        $logger = null
    )
    {
        $this->config = $config ?: Di::_()->get('Config');
        $this->translator = $translator ?:
            new SymfonyTranslator('en', null, null, $this->config->get('development_mode'));

        $this->logger = $logger ?: Di::_()->get('Logger');

        $this->loadResources();
    }

    public function loadResources()
    {
        $this->translator->addLoader('xlf', new XliffFileLoader());
        $languages = array_keys($this->config->get('i18n')['languages']);
        foreach ($languages as $language) {
            $file = getcwd() . "/translations/messages.{$language}.xliff";

            if (!file_exists($file)) {
                $this->logger->warn("Localization resource not found ({$file})");
                continue;
            }

            $this->translator->addResource('xlf', $file, $language);
        }
    }

    public function getTranslator(): SymfonyTranslator
    {
        return $this->translator;
    }

    /**
     * Sets the locale
     * @param string $locale
     * @return $this
     */
    public function setLocale(string $locale): Translator
    {
        $this->translator->setLocale($locale);

        return $this;
    }

    public function translate(?string $id, array $parameters = [], string $domain = null, string $locale = null)
    {
        return $this->translator->trans($id, $parameters, $domain, $locale);
    }
}
