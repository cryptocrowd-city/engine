<?php
/**
 * Config
 *
 * @author edgebal
 */

namespace Minds\Core\Features\Services;

use InvalidArgumentException;
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
        return array_map([$this, '_resolveValue'], $this->config->get('features') ?: []);
    }

    /**
     * @param mixed $value
     * @return bool
     * @throws InvalidArgumentException
     */
    protected function _resolveValue($value): bool
    {
        if (is_string($value)) {
            return in_array(strtolower($value), $this->getUserGroups(), true);
        } elseif (is_bool($value)) {
            return $value;
        }

        throw new InvalidArgumentException();
    }
}
