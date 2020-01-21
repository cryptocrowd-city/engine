<?php
/**
 * Environment
 *
 * @author edgebal
 */

namespace Minds\Core\Features\Services;

/**
 * Environment variables ($_ENV) feature flags service
 * @package Minds\Core\Features\Services
 */
class Environment extends BaseService
{
    /**
     * @inheritDoc
     */
    public function fetch(array $keys): array
    {
        $output = [];

        foreach ($keys as $key) {
            // Convert to variable name
            // Example: `webtorrent` would be `MINDS_FEATURE_WEBTORRENT`; `psr7-router` would be `MINDS_FEATURE_PSR7_ROUTER`

            $envName = sprintf('MINDS_FEATURE_%s', strtoupper(preg_replace('/[^a-zA-Z0-9]+/', '_', $key)));

            if (isset($_ENV[$envName])) {
                // Read value as string

                $value = (string) $_ENV[$envName];

                // Resolve group, if not 0 or 1

                if (strlen($value) > 0 && $value !== '0' && $value !== '1') {
                    $value = in_array(strtolower($value), $this->getUserGroups(), true);
                }

                // Set value

                $output[$key] = (bool) $value;
            }
        }

        return $output;
    }
}
