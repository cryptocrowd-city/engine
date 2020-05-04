<?php
namespace Minds\Core\Wire\SupportTiers\Delegates;

use Minds\Common\Repository\Response;
use Minds\Core\Wire\SupportTiers\Repository;
use Minds\Core\Wire\SupportTiers\SupportTier;
use Minds\Entities\User;

/**
 * Migrate User entity wire_rewards into support tiers
 * @package Minds\Core\Wire\SupportTiers\Delegates
 */
class UserWireRewardsMigrationDelegate
{
    /** @var Repository */
    protected $repository;

    /**
     * UserWireRewardsMigrationDelegate constructor.
     * @param $repository
     */
    public function __construct(
        $repository = null
    ) {
        $this->repository = $repository ?: new Repository();
    }

    /**
     * @param User $user
     * @return Response<SupportTier>
     */
    public function migrate(User $user): Response
    {
    }
}
