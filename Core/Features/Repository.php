<?php
/**
 * Repository
 *
 * @author edgebal
 */

namespace Minds\Core\Features;

use Minds\Core\Config;
use Minds\Core\Di\Di;
use Minds\Core\Session;
use Minds\UnleashClient\Config as UnleashConfig;
use Minds\UnleashClient\Entities\Context;
use Minds\UnleashClient\Unleash;

class Repository
{
    /** @var Config */
    protected $config;

    /** @var Unleash */
    protected $unleash;

    /**
     * Repository constructor.
     * @param Config $config
     * @param Unleash $unleash
     */
    public function __construct(
        $config = null,
        $unleash = null
    ) {
        $this->config = $config ?: Di::_()->get('Config');
        $this->unleash = $unleash ?: $this->initUnleashClient();
    }

    public function initUnleashClient(): Unleash
    {
        $configValues = $this->config->get('unleash');

        $config = new UnleashConfig(
            $configValues['apiUrl'] ?? null,
            $configValues['instanceId'] ?? null,
            $configValues['applicationName'] ?? null,
            $configValues['pollingIntervalSeconds'] ?? null,
            $configValues['metricsIntervalSeconds'] ?? null
        );

        $cache = Di::_()->get('Cache\PsrWrapper');

        return new Unleash($config, null, null, $cache);
    }

    public function fetch(): array
    {
        $currentUserGuid = (string) Session::getLoggedInUserGuid();

        $context = new Context();
        $context
            ->setRemoteAddress($_SERVER['REMOTE_ADDR'])
            ->setHostName($_SERVER['HTTP_HOST']);

        if ($currentUserGuid) {
            $context
                ->setUserId($currentUserGuid);
        }

        $mindsValues = $this->config->get('features') ?: [];
        $unleashValues = $this->unleash
            ->setContext($context)
            ->export();

        return array_merge($unleashValues, $mindsValues);
    }
}
