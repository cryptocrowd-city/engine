<?php
namespace Minds\Core\Wire\SupportTiers;

use Exception;
use Minds\Common\Repository\Response;
use Minds\Entities\User;

/**
 * Wire Support Tiers Manager
 * @package Minds\Core\Wire\SupportTiers
 */
class Manager
{
    /** @var Repository */
    protected $repository;

    /** @var Delegates\UserWireRewardsMigrationDelegate */
    protected $userWireRewardsMigrationDelegate;

    /** @var mixed */
    protected $entity;

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
     * @param mixed $entity
     * @return Manager
     */
    public function setEntity($entity): Manager
    {
        $this->entity = $entity;
        return $this;
    }

    /**
     * Fetches all support tiers for an entity. Polyfills existing Wire Rewards.
     * @return Response<SupportTier>
     * @throws Exception
     */
    public function getAll(): Response
    {
        if (!$this->entity || !$this->entity->guid) {
            throw new Exception('Missing entity');
        }

        $response = $this->repository->getList(
            (new RepositoryGetListOptions())
                ->setEntityGuid((string) $this->entity->guid)
                ->setLimit(5000)
        );

        if (!$response->count() && $this->entity instanceof User && $this->entity->getWireRewards()) {
            // If entity is User and there are Wire Rewards set, migrate
            $response = $this->userWireRewardsMigrationDelegate
                ->migrate($this->entity);
        }

        return $response;
    }
}
