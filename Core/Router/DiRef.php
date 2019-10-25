<?php
/**
 * DiRef
 * @author edgebal
 */

namespace Minds\Core\Router;

use Minds\Traits\MagicAttributes;

/**
 * Class DiRef
 * @package Minds\Core\Router
 * @method string getProvider()
 * @method DiRef setProvider(string $provider)
 * @method string getMethod()
 * @method DiRef setMethod(string $method)
 */
class DiRef
{
    use MagicAttributes;

    /** @var string */
    protected $provider;

    /** @var string */
    protected $method;

    /**
     * DiRef constructor.
     * @param string $provider
     * @param string $method
     */
    public function __construct(string $provider, string $method)
    {
        $this->setProvider($provider);
        $this->setMethod($method);
    }

    /**
     * @param string $provider
     * @param string $method
     * @return DiRef
     */
    public static function _(string $provider, string $method): DiRef
    {
        return new static($provider, $method);
    }
}
