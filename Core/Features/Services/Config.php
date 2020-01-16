<?php
/**
 * Config
 *
 * @author edgebal
 */

namespace Minds\Core\Features\Services;

use Minds\Core\Config as MindsConfig;
use Minds\Core\Di\Di;

class Config extends BaseService
{
    /** @var MindsConfig */
    protected $config;

    /**
     * Config constructor.
     * @param Config $config
     */
    public function __construct(
        $config = null
    ) {
        $this->config = $config ?: Di::_()->get('Config');
    }

    /**
     * @inheritDoc
     */
    public function fetch(): array
    {
        // TODO: Use User context to calculate final values

        return $this->config->get('features') ?: [];
    }
}
