<?php
namespace Minds\Core\Channels\SupportTiers;

use Minds\Traits\MagicAttributes;

/**
 * Repository::getList() options
 * @package Minds\Core\Channels\SupportTiers
 * @method string getUserGuid()
 * @method RepositoryGetListOptions setUserGuid(string $userGuid)
 * @method string getPaymentMethod()
 * @method RepositoryGetListOptions setPaymentMethod(string $paymentMethod)
 * @method string getGuid()
 * @method RepositoryGetListOptions setGuid(string $guid)
 * @method string getOffset()
 * @method RepositoryGetListOptions setOffset(string $offset)
 * @method int getLimit()
 * @method RepositoryGetListOptions setLimit(int $limit)
 */
class RepositoryGetListOptions
{
    use MagicAttributes;

    /** @var string */
    protected $userGuid;

    /** @var string */
    protected $paymentMethod;

    /** @var string */
    protected $guid;

    /** @var string */
    protected $offset = '';

    /** @var int */
    protected $limit = 5000;
}
