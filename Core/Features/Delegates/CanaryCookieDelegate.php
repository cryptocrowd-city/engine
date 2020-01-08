<?php
/**
 * CanaryCookieDelegate
 *
 * @author edgebal
 */

namespace Minds\Core\Features\Delegates;

use Minds\Common\Cookie;

/**
 * Delegate that controls canary cookie setting
 * @package Minds\Core\Features\Delegates
 */
class CanaryCookieDelegate
{
    /** @var Cookie $cookie */
    protected $cookie;

    public function __construct(
        $cookie = null
    ) {
        $this->cookie = $cookie ?: new Cookie();
    }

    /**
     * Sets canary cookie value
     * @param bool $enabled
     */
    public function onCanaryCookie(bool $enabled): void
    {
        $this->cookie
            ->setName('canary')
            ->setValue((int) $enabled)
            ->setExpire(0)
            ->setSecure(true) //only via ssl
            ->setHttpOnly(true) //never by browser
            ->setPath('/')
            ->create();
    }
}
