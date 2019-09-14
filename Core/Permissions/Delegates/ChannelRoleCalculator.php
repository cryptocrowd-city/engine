<?php

namespace Minds\Core\Permissions\Delegates;

use Minds\Traits\MagicAttributes;
use Minds\Core\Permissions\Roles\Roles;
use Minds\Core\Permissions\Roles\Role;

class ChannelRoleCalculator extends BaseRoleCalculator
{
    use MagicAttributes;

    private $channels = [];

    /**
     * Retrieves permissions for an entity relative to the user's role in a channel
     * Retrieves the role from the in memory cache if we've seen this channel before during this request
     * Else checks the user's membership against the channel.
     *
     * @param $entity an entity from a channel
     *
     * @return Role
     */
    public function calculate($entity): Role
    {
        if (isset($this->channels[$entity->getOwnerGuid()])) {
            return $this->channels[$entity->getOwnerGuid()];
        }
        $role = null;
        if ($this->user === null) {
            $role = $this->roles->getRole(Roles::ROLE_LOGGED_OUT);
        } elseif ($entity->getOwnerGuid() === $this->user->getGuid()) {
            $role = $this->roles->getRole(Roles::ROLE_CHANNEL_OWNER);
        } elseif ($this->user->isSubscribed($entity->getOwnerGuid())) {
            $role = $this->roles->getRole(Roles::ROLE_CHANNEL_SUBSCRIBER);
        } else {
            $role = $this->roles->getRole(Roles::ROLE_CHANNEL_NON_SUBSCRIBER);
        }
        $this->channels[$entity->getOwnerGuid()] = $role;

        return $role;
    }
}
