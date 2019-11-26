<?php
/**
 * @author edgebal
 */
namespace Minds\Core;

use Minds\Helpers\Counters as CountersHelper;

class Counters
{
    /**
     * @param mixed $entity
     * @param string $metric
     * @param bool $cache
     * @return int
     */
    public function get($entity, string $metric, $cache = true): int
    {
        return CountersHelper::get($entity, $metric, $cache);
    }
}
