<?php
/**
 * Repository
 *
 * @author edgebal
 */

namespace Minds\Core\Features\Services\Unleash;

use Cassandra\Timestamp;
use Exception;
use Minds\Core\Data\Cassandra\Client;
use Minds\Core\Data\Cassandra\Prepared\Custom;
use Minds\Core\Di\Di;
use Minds\Helpers\Log;
use NotImplementedException;

class Repository
{
    /** @var Client */
    protected $db;

    /**
     * Repository constructor.
     * @param Client $db
     */
    public function __construct(
        $db = null
    ) {
        $this->db = $db ?: Di::_()->get('Database\Cassandra\Cql');
    }

    /**
     * @return Entity[]
     */
    public function getList(): array
    {
        $cql = "SELECT * FROM feature_toggles_cache";

        $prepared = new Custom();
        $prepared->query($cql);

        $entities = [];

        try {
            $rows = $this->db->request($prepared);

            foreach ($rows ?: [] as $row) {
                $entity = new Entity();
                $entity
                    ->setId($row['id'])
                    ->setData(json_decode($row['data'], true))
                    ->setCreatedAt($row['created_at']->time())
                    ->setStaleAt($row['stale_at']->time());
                $entities[] = $entity;
            }
        } catch (Exception $e) {
            Log::warning($e);
        }

        return $entities;
    }

    /**
     * Shortcut method that brings all the data from getList() entities
     * @return array
     */
    public function getAllData()
    {
        return array_map(function (Entity $entity) {
            return $entity->getData();
        }, $this->getList());
    }

    /**
     * @param Entity $entity
     * @return bool
     * @throws Exception
     */
    public function add(Entity $entity): bool
    {
        if (!$entity->getId()) {
            throw new Exception('Invalid Unleash entity name');
        }

        $cql = "INSERT INTO feature_toggles_cache (id, data, created_at, stale_at) VALUES (?, ?, ?, ?)";
        $values = [
            (string) $entity->getId(),
            (string) json_encode($entity->getData()),
            new Timestamp($entity->getCreatedAt()),
            new Timestamp($entity->getStaleAt())
        ];

        $prepared = new Custom();
        $prepared->query($cql, $values);

        try {
            return (bool) $this->db->request($prepared, true);
        } catch (Exception $e) {
            Log::warning($e);
            return false;
        }
    }

    /**
     * @param Entity $entity
     * @return bool
     * @throws Exception
     */
    public function update(Entity $entity): bool
    {
        return $this->add($entity);
    }

    /**
     * @param string $id
     * @return bool
     * @throws NotImplementedException
     */
    public function delete(string $id): bool
    {
        throw new NotImplementedException();
    }
}
