<?php

namespace Minds\Core\Entities\Propagator;

use Minds\Entities\Activity;

/**
 * Properties class that all PropagateProperties delegates should inherit
 * @package Minds\Core\Entities\Propagator
 */
abstract class Properties
{
    /**
     * @var string
     */
    protected $actsOnType = 'any';
    /**
     * @var string
     */
    protected $actsOnSubtype = 'any';
    /**
     * @var bool
     */
    protected $changed = false;

    /**
     * @return string
     */
    public function actsOnType(): string
    {
        return $this->actsOnType;
    }

    /**
     * @return string
     */
    public function actsOnSubType(): string
    {
        return $this->actsOnSubtype;
    }

    /**
     * @param $entity
     * @return bool
     */
    public function willActOnEntity($entity): bool
    {
        if ($this->actsOnType === 'any' || $this->actsOnType === $entity->getType()) {
            return $this->actsOnSubtype === 'any' || $this->actsOnSubtype === $entity->getSubtype();
        }

        return false;
    }

    /**
     * @param $from
     * @param $to
     * @return bool
     */
    protected function valueHasChanged($from, $to): bool
    {
        $changed = $from !== $to;
        $this->changed |= $changed;
        return $changed;
    }

    /**
     * @return bool
     */
    public function changed(): bool
    {
        return $this->changed;
    }

    /**
     * @param $from
     * @param Activity $to
     * @return Activity
     */
    abstract public function toActivity($from, Activity $to): Activity;

    /**
     * @param Activity $from
     * @param $to
     * @return mixed
     */
    abstract public function fromActivity(Activity $from, $to);
}
