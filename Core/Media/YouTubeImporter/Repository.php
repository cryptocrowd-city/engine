<?php
/**
 * @author: eiennohi.
 */

namespace Minds\Core\Media\YouTubeImporter;

use Minds\Common\Repository\Response;
use Minds\Core\Config\Config;
use Minds\Core\Data\ElasticSearch\Client;
use Minds\Core\Data\ElasticSearch\Prepared\Count;
use Minds\Core\Data\ElasticSearch\Prepared\Search;
use Minds\Core\Di\Di;
use Minds\Core\EntitiesBuilder;
use Minds\Entities\Entity;

class Repository
{
    const ALLOWED_STATUSES = ['queued', 'transcoding', 'completed'];

    /** @var Client */
    protected $client;

    /** @var EntitiesBuilder */
    protected $entitiesBuilder;

    /** @var Config */
    protected $config;

    public function __construct(Client $client = null, EntitiesBuilder $entitiesBuilder = null, Config $config = null)
    {
        $this->client = $client ?: Di::_()->get('Database\ElasticSearch');
        $this->entitiesBuilder = $entitiesBuilder ?: Di::_()->get('EntitiesBuilder');
        $this->config = $config ?: Di::_()->get('Config');
    }

    /**
     * Returns saved videos
     * @param array $opts
     * @return Response
     */
    public function getVideos(array $opts): Response
    {
        $opts = array_merge([
            'limit' => 12,
            'offset' => '',
            'user_guid' => null,
            'youtube_id' => null,
            'status' => null,
            'time_created' => [
                'lt' => null,
                'gt' => null,
            ],
        ], $opts);

        if (isset($opts['status'])) {
            if (!in_array($opts['status'], static::ALLOWED_STATUSES, true)) {
                throw new \Exception('Invalid status param');
            }

            $filter[] = [
                'term' => [
                    'transcoding_status' => $opts['status'],
                ],
            ];
        }

        $filter = [];
        $timeCreatedRange = [];

        if (isset($opts['time_created'])) {
            if (isset($opts['time_created']['lt'])) {
                $timeCreatedRange['lt'] = $opts['time_created']['lt'];
            }

            if (isset($opts['time_created']['gt'])) {
                $timeCreatedRange['gt'] = $opts['time_created']['gt'];
            }
        }

        if (count($timeCreatedRange) > 0) {
            $filter[]['range'] = [
                'time_created' => [
                    $timeCreatedRange,
                ],
            ];
        }

        $query = [
            'index' => 'minds_badger',
            'type' => 'object:video',
            'body' => [
                'query' => [
                    'bool' => [
                        'filter' => $filter,
                    ],
                ],
            ],
        ];

        $prepared = new Search();
        $prepared->query($query);

        $result = $this->client->request($prepared);
        //        var_dump($result);
        //        die();

        $response = new Response();

        if (!isset($result) || !(isset($result['hits'])) || !isset($result['hits']['hits'])) {
            return $response;
        }

        $guids = [];
        foreach ($result['hits']['hits'] as $entry) {
            $guids[] = $entry['source']['guid'];
        }

        $response = new Response($this->entitiesBuilder->get(['guid' => $guids]));

        return $response;
    }

    public function checkOwnerEligibility(array $guids): array
    {
        $result = [];
        for ($i = count($guids); $i >= 0; $i--) {
            $guid = $guids[$i];

            /* check for all transcoded videos created in a 24 hour
             * period that correspond to a youtube video */
            $filter = [
                [
                    'range' => [
                        'time_created' => [
                            'lt' => time(),
                            'gte' => strtotime('-1 day'),
                        ],
                    ],
                ],
                [
                    'exists' => [
                        'field' => 'youtube_id',
                    ],
                ],
                [
                    'term' => [
                        'transcoding_status' => 'completed',
                    ],
                ],
                [
                    'term' => [
                        'owner_guid' => $guid,
                    ],
                ],
            ];

            $query = [
                'index' => 'minds_badger',
                'type' => 'object:video',
                'body' => [
                    'query' => [
                        'bool' => [
                            'filter' => $filter,
                        ],
                    ],
                ],
            ];

            $prepared = new Count();
            $prepared->query($query);

            $result = $this->client->request($prepared);
            $count = $result['count'] ?? 0;
            $result[$guid] = $count;
        }

        return $result;
    }
}
