<?php

/**
 * Features Manager
 *
 * @author emi
 */

namespace Minds\Core\Features;

use Minds\Core\Di\Di;
use Minds\Core\Sessions\ActiveSession;

/**
 * Features Manager
 * @package Minds\Core\Features
 */
class Manager
{
    /** @var Services\ServiceInterface[] */
    protected $services;

    /** @var ActiveSession */
    protected $activeSession;

    /**
     * Manager constructor.
     * @param Services\ServiceInterface[] $services
     * @param ActiveSession $activeSession
     */
    public function __construct(
        $services = null,
        $activeSession = null
    ) {
        $this->services = $services ?: [
            new Services\Config(),
            new Services\Unleash(),
            new Services\Environment(),
        ];

        $this->activeSession = $activeSession ?: Di::_()->get('Sessions\ActiveSession');
    }

    /**
     * Checks if a feature is enabled
     * @param string $feature
     * @param bool $default
     * @return bool
     */
    public function has(string $feature, ?bool $default = false): ?bool
    {
        $features = [];

        foreach ($this->services as $service) {
            $features = array_merge(
                $features,
                $service
                    ->setUser($this->activeSession->getUser())
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
                    ->setUser($this->activeSession->getUser())
                    ->fetch()
            );
        }

        return $features;
    }
}
