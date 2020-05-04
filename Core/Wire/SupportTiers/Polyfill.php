<?php
namespace Minds\Core\Wire\SupportTiers;

use Exception;
use Minds\Entities\User;
use Minds\Helpers\Log;

/**
 * User wire_rewards polyfill manager for Support Tiers
 * @package Minds\Core\Wire\SupportTiers
 */
class Polyfill
{
    /** @var Manager */
    protected $manager;

    /** @var Delegates\UserWireRewardsMigrationDelegate */
    protected $userWireRewardsMigrationDelegate;

    /**
     * Polyfill constructor.
     * @param $manager
     * @param $userWireRewardsMigrationDelegate
     */
    public function __construct(
        $manager = null,
        $userWireRewardsMigrationDelegate = null
    ) {
        $this->manager = $manager ?: new Manager();
        $this->userWireRewardsMigrationDelegate = $userWireRewardsMigrationDelegate ?: new Delegates\UserWireRewardsMigrationDelegate();
    }

    /**
     * Transforms Support Tiers into a wire_rewards compatible output. Migrates if needed.
     * @param User $user
     * @return array
     * @throws Exception
     */
    public function process(User $user): array
    {
        if (!$user) {
            return [];
        }

        try {
            return $this->userWireRewardsMigrationDelegate->polyfill(
                $this->manager
                    ->setEntity($user)
                    ->getAll()
            );
        } catch (Exception $e) {
            Log::warning($e);
            return $user->getWireRewards();
        }
    }
}
