<?php
/**
 * RouterRegistry
 * @author edgebal
 */

namespace Minds\Core\Router;

class RouterRegistry
{
    /** @var RouterRegistry */
    protected static $instance;

    /** @var array */
    protected $registry = [];

    /**
     * @return RouterRegistry
     */
    public static function _(): RouterRegistry
    {
        if (!static::$instance) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    /**
     * @param string $method
     * @param string $route
     * @param mixed $binding
     * @return RouterRegistry
     */
    public function register(string $method, string $route, $binding): RouterRegistry
    {
        $method = strtolower($method);

        if (!isset($this->registry[$method])) {
            $this->registry[$method] = [];
        }

        $routerRegistryEntry = new RouterRegistryEntry();
        $routerRegistryEntry
            ->setRoute($route)
            ->setBinding($binding);

        $this->registry[$method][] = $routerRegistryEntry;

        return $this;
    }

    /**
     * @param string $method
     * @param string $route
     * @return RouterRegistryEntry|null
     */
    public function getBestMatch(string $method, string $route):? RouterRegistryEntry
    {
        if (!isset($this->registry[$method]) || !$this->registry[$method]) {
            return null;
        }

        $route = trim($route, '/');

        /** @var RouterRegistryEntry[] $sortedRouterRegistryEntries */
        $sortedRouterRegistryEntries = $this->registry[$method];
        usort($sortedRouterRegistryEntries, [$this, '_routerRegistryEntrySort']);

        foreach ($sortedRouterRegistryEntries as $routerRegistryEntry) {
            if ($routerRegistryEntry->matches($route)) {
                return $routerRegistryEntry;
            }
        }

        return null;
    }

    /**
     * @param RouterRegistryEntry $a
     * @param RouterRegistryEntry $b
     * @return int
     */
    protected function _routerRegistryEntrySort(RouterRegistryEntry $a, RouterRegistryEntry $b)
    {
        if ($a->getDepth() !== $b->getDepth()) {
            return $b->getDepth() - $a->getDepth();
        }

        return $b->getSpecificity() - $a->getSpecificity();
    }
}
