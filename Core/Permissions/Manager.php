<?php

namespace Minds\Core\Permissions;

use Minds\Core\Di\Di;
use Minds\Core\Entities\Actions\Save;
use Minds\Core\Entities\PropagateProperties;
use Minds\Exceptions\StopEventException;

class Manager
{
    /** @var Save */
    protected $save;
    /** @var PropagateProperties */
    protected $propagateProperties;

    public function __construct(
        Save $save = null,
        PropagateProperties $propagateProperties = null
    ) {
        $this->save = $save ?: new Save();
        $this->propagateProperties = $propagateProperties ?? Di::_()->get('PropagateProperties');
    }


    /**
     * Save permissions for an entity and propagate it to linked objects
     * @param mixed $entity a minds entity that implements the save function
     * @param Permissions $permissions the flag to apply to the entity
     * @throws StopEventException
     */
    public function save($entity, Permissions $permissions)
    {
        $entity->setAllowComments($permissions->getAllowComments());

        $this->save
            ->setEntity($entity)
            ->save();

        $this->propagateProperties->from($entity);
    }
}
