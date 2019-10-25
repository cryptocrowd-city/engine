<?php
/**
 * RouterRegistryEntry
 * @author edgebal
 */

namespace Minds\Core\Router;

use Minds\Traits\MagicAttributes;

/**
 * Class RouterRegistryEntry
 * @package Minds\Core\Router
 * @method string getRoute()
 * @method mixed getBinding()
 * @method RouterRegistryEntry setBinding(mixed $binding)
 */
class RouterRegistryEntry
{
    use MagicAttributes;

    /** @var string */
    protected $route;

    /** @var mixed */
    protected $binding;

    /**
     * @param string $route
     * @return RouterRegistryEntry
     */
    public function setRoute(string $route): RouterRegistryEntry {
        $this->route = trim($route, '/');
        return $this;
    }

    /**
     * @return string
     */
    public function getWildcardRoute(): string
    {
        return preg_replace('/\/:[^\/]+/g', '*', $this->route, '/');
    }

    /**
     * @return int
     */
    public function getDepth(): int
    {
        if (!$this->route) {
            return -1;
        }
        
        return substr_count($this->route, '/');
    }

    /**
     * @return int
     */
    public function getSpecificity(): int
    {
        if (!$this->route) {
            return 1;
        }

        $fragments = explode('/', $this->getWildcardRoute());
        $count = count($fragments);
        $specificity = 0;

        for ($i = 0; $i < $count; $i++) {
            if ($fragments[$i] !== '*') {
                $specificity += 2 ** ($count - 1 - $i);
            }
        }

        return $specificity;
    }

    public function matches($route): bool
    {

    }
}
