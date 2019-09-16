<?php
/**
 * Subscriptions Requests Manager
 */
namespace Minds\Core\Subscriptions\Requests;

use Minds\Core\Subscriptions\Requests\Delegates\NotificationsDelegate;
use Minds\Core\Subscriptions\Requests\Delegates\SubscriptionsDelegate;
use Minds\Common\Repository\Response;

class Manager
{
    /** @var Repository */
    private $repository;

    /** @var NotificationsDelegate */
    private $notificationsDelegate;

    /** @var SubscriptionsDelegate */
    private $subscriptionsDelegate;

    public function __construct($repository = null, $notificationsDelegate = null, $subscriptionsDelegate = null)
    {
        $this->repository = $repository ?? new Repository();
        $this->notificationsDelegate = $notificationsDelegate ?? new NotificationsDelegate;
        $this->subscriptionsDelegate = $subscriptionsDelegate ?? new SubscriptionsDelegate;
    }

    /**
     * Return a list of incoming subscription requests
     * @param array $opts
     * @return Response
     */
    public function getIncomingList(array $opts = [])
    {
        $response = $this->repository->getList($opts);
        return $response;
    }

    /**
     * Return a subscription request
     * @param string $urn
     * @return SubscriptionRequest
     */
    public function get(string $urn): ?SubscriptionRequest
    {
        return $this->repository->get($urn);
    }

    /**
     * Add a subscription request
     * @param SubscriptionRequest $subscriptionRequest
     * @return bool
     */
    public function add(SubscriptionRequest $subscriptionRequest): bool
    {
        // Check if exists
        $existing = $this->get($subscriptionRequest->getUrn());
        if ($existing) {
            throw new SubscriptionRequestExistsException();
        }

        $this->repository->add($subscriptionRequest);

        $this->notificationsDelegate->onAdd($subscriptionRequest);

        return true;
    }

    /**
     * Accept a subscription request
     * @param SubscriptionRequest $subscriptionRequest
     * @return bool
     */
    public function accept(SubscriptionRequest $subscriptionRequest): bool
    {
        // Check if exists
        $existing = $this->get($subscriptionRequest->getUrn());
        if (!$existing) {
            throw new SubscriptionRequestDoesntExistException();
        }

        if ($existing->getAccepted() !== null) {
            throw new SubscriptionRequestAlreadyCompletedException();
        }
    
        $subscriptionRequest->setAccepted(true);
        $this->repository->update($subscriptionRequest);

        $this->notificationsDelegate->onAccept($subscriptionRequest);
        $this->subscriptionsDelegate->onAccept($subscriptionRequest);

        return true;
    }

    /**
     * Decline a subscription request
     * @param SubscriptionRequest $subscriptionRequest
     * @return bool
     */
    public function decline(SubscriptionRequest $subscriptionRequest): bool
    {
        // Check if exists
        $existing = $this->get($subscriptionRequest->getUrn());
        if (!$existing) {
            throw new SubscriptionRequestDoesntExistException();
        }

        if ($existing->getAccepted() !== null) {
            throw new SubscriptionRequestAlreadyCompletedException();
        }

        $subscriptionRequest->setAccepted(false);
        $this->repository->update($subscriptionRequest);

        $this->notificationsDelegate->onDecline($subscriptionRequest);

        return true;
    }
}
