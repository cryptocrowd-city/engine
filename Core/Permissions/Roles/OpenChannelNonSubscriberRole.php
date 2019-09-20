<?php

namespace Minds\Core\Permissions\Roles;

class OpenChannelNonSubscriberRole extends BaseRole
{
    public function __construct()
    {
        parent::__construct(Roles::ROLE_OPEN_CHANNEL_NON_SUBSCRIBER);
    }
}
