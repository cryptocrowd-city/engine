<?php
namespace Minds\Core\Channels\SupportTiers;

use Minds\Core\Di\Provider as DiProvider;

/**
 * Channels Support Tiers DI Provider
 * @package Minds\Core\Channels\SupportTiers
 */
class Provider extends DiProvider
{
    /**
     * Registers all module bindings
     */
    public function register(): void
    {
        $this->di->bind('Channels\SupportTiers\Manager', function ($di) {
            return new Manager();
        });
    }
}
