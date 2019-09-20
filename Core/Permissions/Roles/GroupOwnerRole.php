<?php

namespace Minds\Core\Permissions\Roles;

class GroupOwnerRole extends BaseRole
{
    public function __construct()
    {
        parent::__construct(Roles::ROLE_GROUP_OWNER);
        $this->addPermission(Roles::FLAG_CREATE_COMMENT);
        $this->addPermission(Roles::FLAG_CREATE_COMMENT);
        $this->addPermission(Roles::FLAG_EDIT_COMMENT);
        $this->addPermission(Roles::FLAG_DELETE_COMMENT);
    }
}
