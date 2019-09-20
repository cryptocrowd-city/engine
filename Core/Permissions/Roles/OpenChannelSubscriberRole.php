<?php

namespace Minds\Core\Permissions\Roles;

class OpenChannelSubscriberRole extends BaseRole
{
    public function __construct()
    {
        parent::__construct(Roles::ROLE_OPEN_CHANNEL_SUBSCRIBER);
    }
}
