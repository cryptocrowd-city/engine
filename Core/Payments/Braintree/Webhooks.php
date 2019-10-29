<?php
namespace Minds\Core\Payments\Braintree;

use Minds\Core;
use Minds\Core\Guid;
use Minds\Core\Payments;
use Minds\Entities;
use Minds\Core\Di\Di;
use Minds\Core\Blockchain\Transactions\Transaction;
use Minds\Core\Payments\Subscriptions\Subscription;
use Braintree as BT;

class Webhooks
{
    protected $payload;
    protected $signature;
    /** @var BT\WebhookNotification */
    protected $notification;
    /** @var array */
    protected $aliases = [
        BT\WebhookNotification::SUB_MERCHANT_ACCOUNT_APPROVED => 'subMerchantApproved',
        BT\WebhookNotification::SUB_MERCHANT_ACCOUNT_DECLINED => 'subMerchantDeclined',
        'subscription_charged_successfully' => 'subscriptionCharged',
        'subscription_went_active' => 'subscriptionActive',
        BT\WebhookNotification::SUBSCRIPTION_EXPIRED => 'subscriptionExpired',
        BT\WebhookNotification::SUBSCRIPTION_WENT_PAST_DUE => 'subscriptionOverdue',
        BT\WebhookNotification::SUBSCRIPTION_CANCELED => 'subscriptionCanceled',
        'check' => 'check'
    ];
    protected $hooks;

    public function __construct($hooks = null, $braintree = null)
    {
        $this->hooks = $hooks ?: new Payments\Hooks();
        $this->braintree = $braintree;
    }

    /**
     * Set the request payload
     * @param string $payload
     * @return $this
     */
    public function setPayload($payload)
    {
        $this->payload = $payload;
        return $this;
    }

    /**
     * Set the request signature
     * @param string $signature
     * @return $this
     */
    public function setSignature($signature)
    {
        $this->signature = $signature;
        return $this;
    }

    /**
      * Run the notification hook
      * @return $this
      */
    public function run()
    {
        $this->buildNotification();
        $this->routeAlias();
        return $this;
    }

    protected function buildNotification()
    {
        $this->notification = BT\WebhookNotification::parse($this->signature, $this->payload);
    }

    protected function routeAlias()
    {
        if (method_exists($this, $this->aliases[$this->notification->kind])) {
            $method = $this->aliases[$this->notification->kind];
            $this->$method();
        }
    }

    /**
     * @return void
     */
    protected function subMerchantApproved()
    {
        $message = "Congrats, you are now a Minds Merchant";
        Core\Events\Dispatcher::trigger('notification', 'elgg/hook/activity', [
          'to'=>[$this->notification->merchantAccount->id],
          'from' => 100000000000000519,
          'notification_view' => 'custom_message',
          'params' => ['message'=>$message],
          'message'=>$message
        ]);
    }

    /**
     * @return void
     */
    protected function subMerchantDeclined()
    {
        $reason = $this->notification->message;
        $message = "Sorry, we could not approve your Merchant Account: $reason";
        Core\Events\Dispatcher::trigger('notification', 'elgg/hook/activity', [
          'to'=>[$this->notification->merchantAccount->id],
          'from' => 100000000000000519,
          'notification_view' => 'custom_message',
          'params' => ['message'=>$message],
          'message'=>$message
        ]);
    }

    /**
     * @return void
     */
    protected function subscriptionCharged()
    {
        $subscription = (new Subscription())
            ->setId($this->notification->subscription->id)
            ->setBalance($this->notification->subscription->balance)
            ->setPrice($this->notification->subscription->price);

        $db = new Core\Data\Call('user_index_to_guid');
        //find the customer
        $user_guids = $db->getRow("subscription:" . $subscription->getId());
        $user = Entities\Factory::build($user_guids[0]);
        //WalletHelper::createTransaction($user->guid, ($subscription->getPrice() * 1000) * 1.1, null, "Purchase (Recurring)");
        //$this->hooks->onCharged($subscription);

        $transaction = new Transaction();
        $transaction
            ->setUserGuid($user->guid)
            ->setWalletAddress('offchain')
            ->setTimestamp(time())
            ->setTx('cc:bt-' . Guid::build())
            ->setAmount(($subscription->getPrice()) * 1.1 * 10 ** 18)
            ->setContract('offchain:points')
            ->setCompleted(true);

        Di::_()->get('Blockchain\Transactions\Repository')
            ->add($transaction);
    }

    /**
     * @return void
     */
    protected function subscriptionActive()
    {
        $subscription = (new Subscription())
            ->setId($this->notification->subscription->id)
            ->setBalance($this->notification->subscription->balance)
            ->setPrice($this->notification->subscription->price);
        $this->hooks->onActive($subscription);
    }

    /**
     * @return void
     */
    protected function subscriptionExpired()
    {
        $subscription = (new Subscription())
          ->setId($this->notification->subscription->id);
        $this->hooks->onExpired($subscription);
    }

    /**
     * @return void
     */
    protected function subscriprionOverdue()
    {
        $subscription = (new Subscription())
          ->setId($this->notification->subscription->id);
        $this->hooks->onOverdue($subscription);
    }

    /**
     * @return void
     */
    protected function subscriptionCanceled()
    {
        $subscription = (new Subscription())
          ->setId($this->notification->subscription->id);
        $this->hooks->onCanceled($subscription);
    }

    /**
     * @return void
     */
    protected function check()
    {
        error_log("[webook]:: check is OK!");
    }
}
