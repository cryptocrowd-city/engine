<?php

namespace Minds\Core\Permissions\Roles;

class LoggedOutRole extends BaseRole
{
    public function __construct()
    {
        parent::__construct(Roles::ROLE_LOGGED_OUT);
        $this->addPermission(Flags::FLAG_VIEW);
    }
}
