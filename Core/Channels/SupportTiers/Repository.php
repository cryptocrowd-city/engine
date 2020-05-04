<?php
namespace Minds\Core\Channels\SupportTiers;

use Cassandra\Bigint;
use Cassandra\Decimal;
use Minds\Common\Repository\Response;
use Minds\Core\Data\Cassandra\Client;
use Minds\Core\Data\Cassandra\Prepared\Custom;
use Minds\Core\Di\Di;

/**
 * Channels Support Tiers Repository
 * @package Minds\Core\Channels\SupportTiers
 */
class Repository
{
    /** @var Client */
    protected $db;

    /**
     * Repository constructor.
     * @param $db
     */
    public function __construct(
        $db = null
    ) {
        $this->db = $db ?: Di::_()->get('Database\Cassandra\Cql');
    }

    /**
     * Gets a list of all support tiers that match filter options
     * @param RepositoryGetListOptions $opts
     * @return Response<SupportTier>
     */
    public function getList(RepositoryGetListOptions $opts): Response
    {
        $cql = 'SELECT * FROM user_support_tiers';
        $where = [];
        $values = [];

        if ($opts->getUserGuid()) {
            $where[] = 'user_guid = ?';
            $values[] = (string) $opts->getUserGuid();
        }

        if ($opts->getPaymentMethod()) {
            $where[] = 'payment_method = ?';
            $values[] = (string) $opts->getPaymentMethod();
        }

        if ($opts->getGuid()) {
            $where[] = 'guid = ?';
            $values[] = (string) $opts->getGuid();
        }

        if ($where) {
            $cql .= ' ' . implode(' AND ', $where);
        }

        $cqlOpts = [
            'paging_state_token' => base64_decode((string) $opts->getOffset(), true),
            'page_size' => (int) $opts->getLimit(),
        ];

        $prepared = new Custom();
        $prepared->query($cql, $values);
        $prepared->setOpts($cqlOpts);

        $rows = $this->db->request($prepared);
        $response = new Response();

        foreach ($rows ?: [] as $row) {
            $supportTier = new SupportTier();

            $supportTier
                ->setUserGuid((string) $row['user_guid']->value())
                ->setPaymentMethod($row['payment_method'])
                ->setGuid((string) $row['guid']->value())
                ->setAmount((string) $row['amount']->value())
                ->setName($row['name'])
                ->setDescription($row['description']);

            $response[] = $supportTier;
        }

        $response->setPagingToken(base64_encode($rows->pagingStateToken()));
        $response->setLastPage($rows->isLastPage());

        return $response;
    }

    /**
     * Creates a new support tier
     * @param SupportTier $supportTier
     * @return bool
     */
    public function add(SupportTier $supportTier): bool
    {
        $cql = 'INSERT INTO user_support_tiers (user_guid, payment_method, guid, amount, name, description) VALUES (?, ?, ?, ?, ?, ?)';
        $values = [
            new Bigint($supportTier->getUserGuid()),
            (string) $supportTier->getPaymentMethod(),
            new Bigint($supportTier->getGuid()),
            new Decimal($supportTier->getAmount()),
            (string) $supportTier->getName(),
            (string) $supportTier->getDescription(),
        ];

        $prepared = new Custom();
        $prepared->query($cql, $values);

        return (bool) $this->db->request($prepared, true);
    }

    /**
     * Updates a support tier
     * @param SupportTier $supportTier
     * @return bool
     */
    public function update(SupportTier $supportTier): bool
    {
        return $this->add($supportTier);
    }

    /**
     * Deletes a single support tier
     * @param SupportTier $supportTier
     * @return bool
     */
    public function delete(SupportTier $supportTier): bool
    {
        $cql = 'DELETE FROM user_support_tiers WHERE user_guid = ? AND payment_method = ? AND guid = ?';
        $values = [
            new Bigint($supportTier->getUserGuid()),
            (string) $supportTier->getPaymentMethod(),
            new Bigint($supportTier->getGuid()),
        ];

        $prepared = new Custom();
        $prepared->query($cql, $values);

        return (bool) $this->db->request($prepared, true);
    }

    /**
     * Deletes all support tiers from a channel
     * @param SupportTier $supportTier
     * @return bool
     */
    public function deleteAll(SupportTier $supportTier): bool
    {
        $cql = 'DELETE FROM user_support_tiers WHERE user_guid = ?';
        $values = [
            new Bigint($supportTier->getUserGuid()),
        ];

        $prepared = new Custom();
        $prepared->query($cql, $values);

        return (bool) $this->db->request($prepared, true);
    }
}
