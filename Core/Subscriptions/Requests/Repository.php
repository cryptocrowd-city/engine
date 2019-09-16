<?php
/**
 * Subscriptions Requests Repository
 */
namespace Minds\Core\Subscriptions\Requests;

use Minds\Core\Di\Di;
use Minds\Core\Data\Cassandra\Client;
use Minds\Common\Repository\Response;

class Repository
{
    /** @var Client */
    private $db;

    public function __construct($db = null)
    {
        $this->db = $db ?? Di::_()->get('Database\Cassandra\Cql');
    }
 
    /**
     * Return a list of subscription requests
     * @param array $opts
     * @return Response
     */
    public function getList(array $opts = []): Response
    {
        $prepared = new Prepared\Custom();
        $prepared->query(
            "SELECT * FROM subscription_requests
            WHERE publisher_guid = ?",
            [
                $opts['publisher_guid'],
            ]
        );
        $result = $this->db->request($prepared);
        $response = new Response;

        foreach ($result as $row) {
            $subscriptionRequest = new SubscriptionRequest();
            $subscriptionRequest
                ->setPublisherGuid((string) $row['publisher_guid'])
                ->setSubscriberGuid((string) $row['subscriber_guid'])
                ->setAccepted((bool) $row['accepted'])
                ->setTimestampMs((int) $row['timestamp']->time());
            $result[] = $subscriptionRequest;
        }

        return $response;
    }

    /**
     * Return a single subscription request from a urn
     * @param string $urn
     * @return SubscriptionRequest
     */
    public function get(string $urn): ?SubscriptionRequest
    {
        $urn = new Urn($urn);
        list($publisherGuid, $subscriberGuid) = explode('-', $urn->getNss());
 
        $prepared = new Prepared\Custom();
        $prepared->query(
            "SELECT * FROM subscription_requests
            WHERE publisher_guid = ?
            AND subscriber_guid = ?",
            [
                $publisherGuid,
                $subscriberGuid,
            ]
        );
        $result = $this->db->request($prepared);

        if (!$result) {
            return null;
        }

        $row = $result[0];

        $subscriptionRequest = new SubscriptionRequest();
        $subscriptionRequest
                ->setPublisherGuid((string) $row['publisher_guid'])
                ->setSubscriberGuid((string) $row['subscriber_guid'])
                ->setAccepted((bool) $row['accepted'])
                ->setTimestampMs((int) $row['timestamp']->time());

        return $subscriptionRequest;
    }

    /**
     * Add a subscription request
     * @param SubscriptionRequest $subscriptionRequest
     * @return bool
     */
    public function add(SubscriptionRequest $subscriptionRequest): bool
    {
        $statement = "INSERT INTO subscription_requests (publisher_guid, subscriber_guid, timestamp) VALUES (?, ?, ?)";
        $values = [
            new Bigint($subscriptionRequest->getPublisherGuid()),
            new Bigint($subscriptionRequest->getSubscriberGuid()),
            new Timestamp($subscriptionRequest->getTimestamp() ?? round(microtime(true) * 1000)),
        ];

        $prepared = new Custom\Prepared();
        $prepared->query($statement, $values);

        return (bool) $this->db->request($prepared);
    }

    /**
     * Update a subscription request
     * @param SubscriptionRequest $subscriptionRequest
     * @param array $fields
     * @return bool
     */
    public function update(SubscriptionRequest $subscriptionRequest, array $field = []): bool
    {
        $statement = "INSERT INTO subscription_requests (publisher_guid, subscriber_guid, timestamp) VALUES (?, ?, ?)";
        $values = [
            new Bigint($subscriptionRequest->getPublisherGuid()),
            new Bigint($subscriptionRequest->getSubscriberGuid()),
            new Timestamp($subscriptionRequest->getTimestamp() ?? round(microtime(true) * 1000)),
        ];

        $prepared = new Custom\Prepared();
        $prepared->query($statement, $values);

        return (bool) $this->db->request($prepared);
    }

    /**
     * Delete a subscription request
     * @param SubscriptionRequest $subscriptionRequest
     * @return bool
     */
    public function delete(SubscriptionRequest $subscriptionRequest): bool
    {
        $statement = "DELETE FROM subscription_requests
            WHERE publisher_guid = ?
            AND subscriber_guid = ?";
        $values = [
            new Bigint($subscriptionRequest->getPublisherGuid()),
            new Bigint($subscriptionRequest->getSubscriberGuid()),
        ];

        $prepared = new Custom\Prepared();
        $prepared->query($statement, $values);

        return (bool) $this->db->request($prepared);
    }
}
