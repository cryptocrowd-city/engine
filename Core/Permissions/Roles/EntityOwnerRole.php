<?php

namespace Minds\Core\Permissions\Roles;

class EntityOwnerRole extends BaseRole
{
    public function __construct()
    {
        parent::__construct(Roles::ROLE_ENTITY_OWNER);
        $this->addPermission(Roles::FLAG_EDIT_POST);
        $this->addPermission(Roles::FLAG_DELETE_POST);
        $this->addPermission(Roles::FLAG_VIEW);
        $this->addPermission(Roles::FLAG_CREATE_COMMENT);
        $this->addPermission(Roles::FLAG_EDIT_COMMENT);
        $this->addPermission(Roles::FLAG_DELETE_COMMENT);
        $this->addPermission(Roles::FLAG_VOTE);
        $this->addPermission(Roles::FLAG_REMIND);
        $this->addPermission(Roles::FLAG_WIRE);
    }
}
