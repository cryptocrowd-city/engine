<?php

namespace Minds\Core\Permissions\Roles;

class ModeratedChannelSubscriberRole extends BaseRole
{
    public function __construct()
    {
        parent::__construct(Roles::ROLE_MODERATED_CHANNEL_SUBSCRIBER);
        $this->addPermission(Roles::FLAG_CREATE_POST);
        $this->addPermission(Roles::FLAG_VIEW);
        $this->addPermission(Roles::FLAG_VOTE);
        $this->addPermission(Roles::FLAG_CREATE_COMMENT);
        $this->addPermission(Roles::FLAG_REMIND);
        $this->addPermission(Roles::FLAG_WIRE);
        $this->addPermission(Roles::FLAG_MESSAGE);
        $this->addPermission(Roles::FLAG_INVITE);
    }
}
