<?php
namespace Minds\Core\Wire\SupportTiers;

use Exception;
use Minds\Common\Repository\Response;
use Minds\Core\GuidBuilder;
use Minds\Entities\User;

/**
 * Wire Support Tiers Manager
 * @package Minds\Core\Wire\SupportTiers
 */
class Manager
{
    /** @var Repository */
    protected $repository;

    /** @var GuidBuilder */
    protected $guidBuilder;

    /** @var Delegates\UserWireRewardsMigrationDelegate */
    protected $userWireRewardsMigration;

    /** @var mixed */
    protected $entity;

    /**
     * Manager constructor.
     * @param $repository
     * @param $guidBuilder
     * @param $userWireRewardsMigration
     */
    public function __construct(
        $repository = null,
        $guidBuilder = null,
        $userWireRewardsMigration = null
    ) {
        $this->repository = $repository ?: new Repository();
        $this->guidBuilder = $guidBuilder ?: new GuidBuilder();
        $this->userWireRewardsMigration = $userWireRewardsMigration ?: new Delegates\UserWireRewardsMigrationDelegate();
    }

    /**
     * @param mixed $entity
     * @return Manager
     */
    public function setEntity($entity): Manager
    {
        // TODO: Check entity type
        $this->entity = $entity;
        return $this;
    }

    /**
     * Fetches all support tiers for an entity.
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

        if (!$response->count() && $this->entity instanceof User) {
            $response = $this->userWireRewardsMigration->migrate($this->entity, true);
        }

        return $response;
    }

    /**
     * Gets a single Support Tier based on partial data
     * @param SupportTier $supportTier
     * @return SupportTier|null
     * @throws Exception
     */
    public function get(SupportTier $supportTier): ?SupportTier
    {
        if (!$supportTier->getEntityGuid() || !$supportTier->getGuid()) {
            throw new Exception('Missing primary key');
        }

        return $this->repository->getList(
            (new RepositoryGetListOptions())
                ->setEntityGuid($supportTier->getEntityGuid())
                ->setGuid($supportTier->getGuid())
                ->setLimit(1)
        )->first();
    }

    /**
     * Creates a new Support Tier
     * @param SupportTier $supportTier
     * @return SupportTier|null
     * @throws \Minds\Exceptions\StopEventException
     */
    public function create(SupportTier $supportTier): ?SupportTier
    {
        $supportTier
            ->setGuid($this->guidBuilder->build());

        $success = $this->repository->add($supportTier);

        return $success ? $supportTier : null;
    }

    /**
     * Updates a Support Tier
     * @param SupportTier $supportTier
     * @return SupportTier|null
     * @throws \Minds\Exceptions\StopEventException
     */
    public function update(SupportTier $supportTier): ?SupportTier
    {
        $success = $this->repository->update($supportTier);

        return $success ? $supportTier : null;
    }

    /**
     * Deletes a Support Tier
     * @param SupportTier $supportTier
     * @return bool
     * @throws \Minds\Exceptions\StopEventException
     */
    public function delete(SupportTier $supportTier): bool
    {
        return $this->repository->delete($supportTier);
    }
}
