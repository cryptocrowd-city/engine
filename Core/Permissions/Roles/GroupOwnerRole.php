<?php

namespace Minds\Core\Permissions\Roles;

class GroupOwnerRole extends BaseRole
{
    public function __construct()
    {
        parent::__construct(Roles::ROLE_GROUP_OWNER);
        $this->addPermission(Roles::FLAG_APPOINT_ADMIN);
        $this->addPermission(Roles::FLAG_CREATE_POST);
        $this->addPermission(Roles::FLAG_EDIT_POST);
        $this->addPermission(Roles::FLAG_DELETE_POST);
        $this->addPermission(Roles::FLAG_APPOINT_MODERATOR);
        $this->addPermission(Roles::FLAG_APPROVE_SUBSCRIBER);
        $this->addPermission(Roles::FLAG_SUBSCRIBE);
        $this->addPermission(Roles::FLAG_VIEW);
        $this->addPermission(Roles::FLAG_VOTE);
        $this->addPermission(Roles::FLAG_CREATE_COMMENT);
        $this->addPermission(Roles::FLAG_EDIT_COMMENT);
        $this->addPermission(Roles::FLAG_DELETE_COMMENT);
        $this->addPermission(Roles::FLAG_REMIND);
        $this->addPermission(Roles::FLAG_WIRE);
        $this->addPermission(Roles::FLAG_MESSAGE);
        $this->addPermission(Roles::FLAG_INVITE);
        $this->addPermission(Roles::FLAG_CREATE_GROUP);
        $this->addPermission(Roles::FLAG_EDIT_GROUP);
        $this->addPermission(Roles::FLAG_DELETE_GROUP);
        $this->addPermission(Roles::FLAG_JOIN);
        $this->addPermission(Roles::FLAG_JOIN_GATHERING);
    }
}
