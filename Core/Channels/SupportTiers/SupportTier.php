<?php
namespace Minds\Core\Channels\SupportTiers;

use JsonSerializable;
use Minds\Helpers\Urn;
use Minds\Traits\MagicAttributes;

/**
 * Support Tier Entity
 * @package Minds\Core\Channels\SupportTiers
 * @method string getUserGuid()
 * @method SupportTier setUserGuid(string $userGuid)
 * @method string getPaymentMethod()
 * @method SupportTier setPaymentMethod(string $paymentMethod)
 * @method string getGuid()
 * @method SupportTier setGuid(string $guid)
 * @method float getAmount()
 * @method SupportTier setAmount(float $amount)
 * @method string getName()
 * @method SupportTier setName(string $name)
 * @method string getDescription()
 * @method SupportTier setDescription(string $description)
 */
class SupportTier implements JsonSerializable
{
    use MagicAttributes;

    /** @var string */
    protected $userGuid;

    /** @var string */
    protected $paymentMethod;

    /** @var string */
    protected $guid;

    /** @var float */
    protected $amount;

    /** @var string */
    protected $name;

    /** @var string */
    protected $description;

    /**
     * Exports the tier into an associative array
     * @return array
     */
    public function export(): array
    {
        $urn = null;

        if ($this->userGuid && $this->paymentMethod && $this->guid) {
            $urn = Urn::build('support-tier', [
                $this->userGuid,
                $this->paymentMethod,
                $this->guid,
            ]);
        }

        return [
            'urn' => $urn,
            'user_guid' => $this->userGuid,
            'payment_method' => $this->paymentMethod,
            'guid' => $this->guid,
            'amount' => $this->amount,
            'name' => $this->name,
            'description' => $this->description,
        ];
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        return $this->export();
    }
}
