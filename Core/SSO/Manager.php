<?php
/**
 * Manager
 * @author edgebal
 */

namespace Minds\Core\SSO;

use Minds\Core\Data\cache\abstractCacher;
use Minds\Core\Di\Di;

class Manager
{
    /** @var abstractCacher */
    protected $cache;

    /** @var Delegates\ProDelegate */
    protected $proDelegate;

    /** @var string */
    protected $domain;

    /**
     * Manager constructor.
     * @param abstractCacher $cache
     * @param Delegates\ProDelegate $proDelegate
     */
    public function __construct(
        $cache = null,
        $proDelegate = null
    )
    {
        $this->cache = $cache ?: Di::_()->get('Cache');
        $this->proDelegate = $proDelegate ?: new Delegates\ProDelegate();
    }

    /**
     * @param string $domain
     * @return Manager
     */
    public function setDomain(string $domain): Manager
    {
        $this->domain = $domain;
        return $this;
    }

    /**
     * @return bool
     */
    public function isAllowed(): bool
    {
        if ($this->proDelegate->isAllowed($this->domain)) {
            return true;
        }

        return false;
    }

    /**
     * @return string
     */
    public function generateToken(): string
    {
        return $this->domain;
    }

    /**
     * @param string $token
     * @return bool
     */
    public function verify(string $token): bool
    {

    }
}
