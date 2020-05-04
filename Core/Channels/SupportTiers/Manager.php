<?php
namespace Minds\Core\Channels\SupportTiers;

use Exception;
use Minds\Common\Repository\Response;
use Minds\Entities\User;

/**
 * Channels Support Tiers Manager
 * @package Minds\Core\Channels\SupportTiers
 */
class Manager
{
    /** @var Repository */
    protected $repository;

    /** @var Delegates\UserWireRewardsMigrationDelegate */
    protected $userWireRewardsMigrationDelegate;

    /** @var User */
    protected $user;

    /**
     * Manager constructor.
     * @param $repository
     * @param $userWireRewardsMigrationDelegate
     */
    public function __construct(
        $repository = null,
        $userWireRewardsMigrationDelegate = null
    ) {
        $this->repository = $repository ?: new Repository();
        $this->userWireRewardsMigrationDelegate = $userWireRewardsMigrationDelegate ?: new Delegates\UserWireRewardsMigrationDelegate();
    }

    /**
     * @param User $user
     * @return Manager
     */
    public function setUser(User $user): Manager
    {
        $this->user = $user;
        return $this;
    }

    /**
     * Fetches all support tiers for a user. Polyfills existing Wire Rewards.
     * @return Response<SupportTier>
     * @throws Exception
     */
    public function getAll(): Response
    {
        if (!$this->user) {
            throw new Exception('Missing User');
        }

        $response = $this->repository->getList(
            (new RepositoryGetListOptions())
                ->setUserGuid((string) $this->user->guid)
                ->setLimit(5000)
        );

        if (!$response->count() && $this->user->getWireRewards()) {
            $response = $this->userWireRewardsMigrationDelegate
                ->migrate($this->user);
        }

        return $response;
    }
}
