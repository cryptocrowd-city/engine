<?php
namespace Minds\Core\Channels\SupportTiers;

use Minds\Interfaces\ModuleInterface;

/**
 * Channels Support Tiers Module
 * @package Minds\Core\Channels\SupportTiers
 */
class Module implements ModuleInterface
{
    /**
     * @inheritDoc
     */
    public function onInit()
    {
        // DI Provider
        (new Provider())->register();
    }
}
