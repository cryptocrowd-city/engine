<?php

namespace Minds\Core\Permissions\Roles;

class ClosedGroupSubscriberRole extends BaseRole
{
    public function __construct()
    {
        parent::__construct(Roles::ROLE_CLOSED_GROUP_SUBSCRIBER);
    }
}
