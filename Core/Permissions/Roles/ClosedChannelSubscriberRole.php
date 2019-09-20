<?php

namespace Minds\Core\Permissions\Roles;

class ClosedChannelSubscriberRole extends BaseRole
{
    public function __construct()
    {
        parent::__construct(Roles::ROLE_CLOSED_CHANNEL_SUBSCRIBER);
    }
}
