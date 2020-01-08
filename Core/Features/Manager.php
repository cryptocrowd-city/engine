<?php

/**
 * Features Manager
 *
 * @author emi
 */

namespace Minds\Core\Features;

use Minds\Entities\User;

class Manager
{
    /** @var Repository */
    protected $repository;

    /** @var Delegates\CanaryCookieDelegate */
    protected $canaryCookie;

    /** @var User */
    protected $user;

    /**
     * Manager constructor.
     * @param Repository $repository
     * @param Delegates\CanaryCookieDelegate $canaryCookie
     */
    public function __construct(
        $repository = null,
        $canaryCookie = null
    ) {
        $this->repository = $repository ?: new Repository();
        $this->canaryCookie = $canaryCookie ?: new Delegates\CanaryCookieDelegate();
    }

    /**
     * Sets the user
     * @param User $user
     * @return Manager
     */
    public function setUser($user): Manager
    {
        $this->user = $user;
        return $this;
    }

    /**
     * Checks if a featured is enabled
     * @param $feature
     * @return bool
     */
    public function has($feature): bool
    {
        $features = $this->repository
            ->fetch();

        if (!isset($features[$feature])) {
            return false;
        }

        if ($features[$feature] === 'admin' && $this->user->isAdmin()) {
            return true;
        }

        if ($features[$feature] === 'canary' && $this->user && $this->user->get('canary')) {
            return true;
        }

        return $features[$feature] === true;
    }

    /**
     * Exports the whole features array
     * @return array
     */
    public function export(): array
    {
        return $this->repository->fetch();
    }

    /**
     * Sets the canary cookie
     * @param bool $enabled
     * @return void
     */
    public function setCanaryCookie(bool $enabled = true): void
    {
        $this->canaryCookie
            ->onCanaryCookie($enabled);
    }
}
