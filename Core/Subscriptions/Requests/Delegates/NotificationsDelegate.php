<?php
namespace Minds\Core\Subscriptions\Requests\Delegates;

use Minds\Core\Subscriptions\Requests\SubscriptionRequest;

class NotificationsDelegate
{
    /**
     * Called when subscription request is made
     * @param SubscriptionRequest $subscriptionRequest
     * @return void
     */
    public function onAdd(SubscriptionRequest $subscriptionRequest): void
    {
        // TODO
    }

    /**
     * Called when subscription request is accepted
     * @param SubscriptionRequest $subscriptionRequest
     * @return void
     */
    public function onAccept(SubscriptionRequest $subscriptionRequest): void
    {
        // TODO
    }

    /**
     * Called when subscription request is declined
     * @param SubscriptionRequest $subscriptionRequest
     * @return void
     */
    public function onDecline(SubscriptionRequest $subscriptionRequest): void
    {
        // TODO
    }
}
