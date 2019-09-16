<?php
/**
 * Subscriptions Requests Manager
 */
namespace Minds\Core\Subscriptions\Requests;

class Manager
{
    public function add(SubscriptionRequest $subscriptionRequest): bool
    {
        // Check if exists
        $this->repository->add($subscriptionRequest);
    }

    public function accept(SubscriptionRequest $subscriptionRequest): bool
    {
        // Check if exists
        $subscriptionRequest->setAccepted(true);
        $this->repository->add($subscriptionRequest);
    }

    public function decline(SubscriptionRequest $subscriptionRequest): bool
    {
        // Check if exists
        $subscriptionRequest->setAccepted(false);
        $this->repository->add($subscriptionRequest);
    }
}
