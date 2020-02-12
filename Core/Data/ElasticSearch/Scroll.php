<?php
/**
 * Scroll
 * @author mark
 */

namespace Minds\Core\Data\ElasticSearch;

use Minds\Core\Data\Interfaces\PreparedInterface;
use Minds\Core\Di\Di;

class Scroll
{
    /** @var Client */
    protected $client;

    const ES_DEFAULT_SCROLL_TIME = "60s";

    /**
     * Scroll constructor.
     * @param Client $client
     */
    public function __construct(
        $client = null
    ) {
        $this->client = $client ?: Di::_()->get('Database\ElasticSearch');
    }

    /**
     * @param PreparedInterface $prepared
     * @return \Generator
     */
    public function request(PreparedInterface $prepared)
    {
        $query = $prepared->build();
        if (!isset($query['scroll'])) {
            $query['scroll'] = self::ES_DEFAULT_SCROLL_TIME;
        }

        $response = $this->client->getClient()->search($query);

        // Now we loop until the scroll "cursors" are exhausted
        while (isset($response['hits']['hits']) && count($response['hits']['hits']) > 0) {
            foreach ($response['hits']['hits'] as $doc) {
                yield $doc;
            }

            $scroll_id = $response['_scroll_id'];

            $response = $this->client->getClient()->scroll(
                [
                    "scroll_id" => $scroll_id,
                    "scroll" => self::ES_DEFAULT_SCROLL_TIME
                ]
            );
        }
    }
}
