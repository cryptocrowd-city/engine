<?php
/**
 * @author: eiennohi.
 */

namespace Minds\Core\DeferredOps;

use Cassandra\Timeuuid;
use Minds\Common\Repository\Response;
use Minds\Common\Urn;
use Minds\Core\Data\Cassandra\Client;
use Minds\Core\Data\Cassandra\Prepared\Custom;
use Minds\Core\Di\Di;

class Repository
{
    /**
     * @var Client
     */
    protected $db;

    public function __construct($db = null)
    {
        $this->db = $db ?: Di::_()->get('Database\Cassandra\Cql');

    }

    /**
     * @param array $opts
     * @return Response
     * @throws \Exception
     */
    public function getList(array $opts = [])
    {
        $opts = array_merge([
            'job' => null,
            'next_attempt' => [
                'lt' => null,
                'lte' => null,
                'gt' => null,
                'gte' => null,
                'eq' => null,
            ],
            'limit' => 12,
            'offset' => null,
        ]);

        $template = 'SELECT * FROM deferred_ops';
        $where = [];
        $values = [];

        if (!isset($opts['job'])) {
            throw new \Exception('job is required');
        }

        $where[] = 'job = ?';
        $values[] = $opts['job'];

        if (isset($opts['next_attempt'])) {
            $operator = '';
            $operators = ['lt', 'lte', 'gt', 'gt', 'eq'];

            foreach ($operators as $op) {
                if (isset($opts['next_attempt'][$op]) && $opts['next_attempt'][$op]) {
                    $operator = $op;
                    break;
                }
            }

            $where[] = "next attempt {$operator} ?";
            $values[] = $opts['next_attempt'][$operator];
        }

        $template .= " WHERE " . implode(" AND ", $where);

        $query = new Custom();
        $query->query($template, $values);

        $query->setOpts([
            'page_size' => (int) $opts['limit'],
            'paging_state_token' => $opts['offset'],
        ]);

        $result = null;

        try {
            $result = $this->db->request($query);
        } catch (\Exception $e) {
            error_log($e);
        }

        $response = new Response();

        if ($result) {
            foreach ($result as $row) {
                $op = (new DeferredOp())
                    ->setJob($row['job'])
                    ->setNextAttempt($row['next_attempt'])
                    ->setEntityUrn(new Urn($row['urn']))
                    ->setRetries($row['retries'])
                    ->setMaxRetries($row['max_retries']);

                $response[] = $op;
            }

            $response->setPagingToken(base64_encode($result->pagingStateToken()));
        }

        return $response;
    }

    /**
     * @param DeferredOp $op
     * @return bool|\Cassandra\FutureRows|\Cassandra\Rows|null
     * @throws \Exception
     */
    public function add(DeferredOp $op)
    {
        if (!$op->getJob()) {
            throw new \Exception('job is required');
        }

        if (!$op->getNextAttempt()) {
            throw new \Exception('next_attempt is required');
        }

        if (!$op->getEntityUrn()) {
            throw new \Exception('entity_urn is required');
        }

        $template = "INSERT INTO deferred_ops (job, next_attempt, entity_urn, retries, max_retries) VALUES (?, ?, ?, ?, ?)";
        $values = [
            (string) $op->getJob(),
            new Timeuuid($op->getNextAttempt()),
            (string) $op->getEntityUrn()->getUrn(),
            (int) $op->getRetries(),
            (int) $op->getMaxRetries(),
        ];

        $query = new Custom();
        $query->query($template, $values);

        return $this->db->request($query);
    }

    /**
     * @param DeferredOp $op
     * @return bool|\Cassandra\FutureRows|\Cassandra\Rows|null
     * @throws \Exception
     */
    public function delete(DeferredOp $op)
    {
        if (!$op->getJob()) {
            throw new \Exception('job is required');
        }

        if (!$op->getNextAttempt()) {
            throw new \Exception('next_attempt is required');
        }

        if (!$op->getEntityUrn()) {
            throw new \Exception('entity_urn is required');
        }

        $template = "DELETE FROM deferred_ops WHERE job = ? AND next_attempt = ? AND entity_urn = ?";
        $values = [
            (string) $op->getJob(),
            new Timeuuid($op->getNextAttempt()),
            (string) $op->getEntityUrn()->getUrn(),
        ];

        $query = new Custom();
        $query->query($template, $values);

        return $this->db->request($query);
    }

}
