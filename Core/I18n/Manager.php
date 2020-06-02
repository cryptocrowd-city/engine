<?php
namespace Minds\Core\I18n;

use Locale;
use Minds\Common\Cookie;
use Minds\Core\Config;
use Minds\Core\Di\Di;
use Minds\Core\Session;

/**
 * i18n Manager
 * @package Minds\Core\I18n
 */
class Manager
{
    /** @var string */
    const DEFAULT_LANGUAGE = 'en';

    /** @var Config */
    protected $config;

    /**
     * Manager constructor.
     * @param null $config
     */
    public function __construct($config = null)
    {
        $this->config = $config ?: Di::_()->get('Config');
    }

    /**
     * Gets all set-up languages
     * @return array
     */
    public function getLanguages(): array
    {
        return Locales::I18N_LOCALES;
    }

    /**
     * Get the current user's language, unless overriden
     * @return string
     */
    public function getLanguage(): string
    {
        $user = Session::getLoggedInUser();

        if (!$user) {
            return $this->getPrimaryLanguageFromHeader() ?: static::DEFAULT_LANGUAGE;
        }

        return $user->getLanguage()
            ?? $this->getPrimaryLanguageFromHeader()
            ?? static::DEFAULT_LANGUAGE;
    }

    /**
     * Returns if the language is a valid language
     * @param string $language
     * @return bool
     */
    public function isLanguage(string $language): bool
    {
        $localeCodes = array_map(function ($locale) {
            return strtolower($locale['code']);
        }, Locales::I18N_LOCALES);

        return in_array($language, $localeCodes);
    }

    /**
     * Gets the language from the query string, if valid
     * @return null|string
     */
    public function getLanguageFromQueryString()
    {
        if (!isset($_GET['hl']) || !$this->isLanguage($_GET['hl'])) {
            return null;
        }

        return strtolower($_GET['hl']);
    }

    /**
     * Gets the language from the header, if valid
     * @return null|string
     */
    public function getLanguageFromHeader(): string
    {
        return Locale::acceptFromHttp($_SERVER['HTTP_ACCEPT_LANGUAGE']);
    }

    /**
     * Gets primary language, e.g. en_GB becomes just en.
     * @param {string} $language - en_GB etc.
     * @return string - returns primary language.
     */
    public function getPrimaryLanguageFromHeader(): string
    {
        return Locale::getPrimaryLanguage($this->getLanguageFromHeader());
    }

    /**
     * TODO: remove from router
     */
    public function serveIndex(): void
    {
    }

    /**
     * Sets the language cookie.
     * @param string $language - the value of the cookie.
     * @return void
     */
    public function setLanguageCookie(string $language): void
    {
        $cookie = new Cookie();
        $cookie
            ->setName('hl')
            ->setValue($language)
            ->setExpire(strtotime('+1 year'))
            ->setPath('/')
            ->setHttpOnly(false)
            ->create();

        $_COOKIE['hl'] = $language;
    }
}
