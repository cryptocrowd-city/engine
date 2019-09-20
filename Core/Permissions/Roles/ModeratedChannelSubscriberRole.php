<?php

namespace Minds\Core\Permissions\Roles;

class ModeratedChannelSubscriberRole extends BaseRole
{
    public function __construct()
    {
        parent::__construct(Roles::ROLE_MODERATED_CHANNEL_SUBSCRIBER);
    }
}
