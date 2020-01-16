<?php

/**
 * Features Manager
 *
 * @author emi
 */

namespace Minds\Core\Features;

use Minds\Entities\User;

/**
 * Features Manager
 * @package Minds\Core\Features
 */
class Manager
{
    /** @var Services\ServiceInterface[] */
    protected $services;

    /** @var User */
    protected $user;

    /**
     * Manager constructor.
     * @param Services\ServiceInterface[] $services
     */
    public function __construct(
        $services = null
    ) {
        $this->services = $services ?: [
            new Services\Config(),
            new Services\Unleash(),
            new Services\Environment(),
        ];
    }

    /**
     * Sets the current user for context
     * @param User|null $user
     * @return Manager
     */
    public function setUser(?User $user): Manager
    {
        $this->user = $user;
        return $this;
    }

    /**
     * Checks if a feature is enabled
     * @param string $feature
     * @param bool $default
     * @return bool
     */
    public function has(string $feature, bool $default = false): bool
    {
        $features = [];

        foreach ($this->services as $service) {
            $features = array_merge(
                $features,
                $service
                    ->setUser($this->user)
                    ->fetch()
            );
        }

        if (!isset($features[$feature])) {
            return $default;
        }

        return (bool) $features[$feature];
    }

    /**
     * Exports the whole features array
     * @return array
     */
    public function export(): array
    {
        $features = [];

        foreach ($this->services as $service) {
            $features = array_merge(
                $features,
                $service
                    ->setUser($this->user)
                    ->fetch()
            );
        }

        return $features;
    }
}
