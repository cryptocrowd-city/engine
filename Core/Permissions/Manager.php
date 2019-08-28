<?php

namespace Minds\Core\Permissions;

use Minds\Core\Di\Di;
use Minds\Core\Entities\Actions\Save;
use Minds\Exceptions\StopEventException;

class Manager
{
    /** @var Save */
    protected $save;
    /** @var \Minds\Core\Entities\PropogateProperties */
    protected $propogateProperties;

    public function __construct(
        Save $save = null,
        \Minds\Core\Entities\PropogateProperties $propogateProperties = null
    )
    {
        $this->save = $save ?: new Save();
        $this->propogateProperties = $propogateProperties ?? Di::_()->get('PropogateProperties');
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

        $this->propogateProperties->from($entity);
    }
}
