<?php

namespace Minds\Core\Permissions\Roles;

class OpenGroupSubscriberRole extends BaseRole
{
    public function __construct()
    {
        parent::__construct(Roles::ROLE_OPEN_GROUP_SUBSCRIBER);
    }
}
