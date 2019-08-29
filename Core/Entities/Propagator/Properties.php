<?php

namespace Minds\Core\Entities\Propagator;

use Minds\Entities\Activity;

abstract class Properties
{
    protected $actsOnType = 'any';
    protected $actsOnSubtype = 'any';
    protected $changed = false;

    public function actsOnType(): string
    {
        return $this->actsOnType;
    }

    public function actsOnSubType(): string
    {
        return $this->actsOnSubtype;
    }

    public function willActOnEntity($entity): bool
    {
        if ($this->actsOnType === 'any' || $this->actsOnType === $entity->getType()) {
            return $this->actsOnSubtype === 'any' || $this->actsOnSubtype === $entity->getSubtype();
        }

        return false;
    }

    protected function valueHasChanged($from, $to): bool
    {
        $changed = $from !== $to;
        $this->changed |= $changed;
        return $changed;
    }

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
