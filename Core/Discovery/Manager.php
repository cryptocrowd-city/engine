<?php
namespace Minds\Core\Discovery;

use Minds\Core\Di\Di;
use Minds\Core\Session;
use Minds\Core;
use Minds\Core\Data\ElasticSearch;
use Minds\Common\Repository\Response;
use Minds\Api\Exportable;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\Response\JsonResponse;

class Manager
{
    private $tagCloud = [];

    /** @var Client */
    private $es;

    /** @var EntitiesBuilder */
    private $entitiesBuilder;

    public function __construct($es = null, $entitiesBuilder = null)
    {
        $this->es = $es ?? Di::_()->get('Database\ElasticSearch');
        $this->entitiesBuilder = $entitiesBuilder ?? Di::_()->get('EntitiesBuilder');
    }

    /**
     * Return the overview for discovery
     * @param array $opts (optional)
     * @return Trend[]
     */
    public function getTagTrends(array $opts = []): array
    {
        $opts = array_merge([
            'limit' => 10,
        ], $opts);

        $this->tagCloud = array_map(function ($tag) {
            return $tag['value'];
        }, Di::_()->get('Hashtags\User\Manager')
            ->setUser(Session::getLoggedInUser())
            ->get([
                'defaults' => false,
            ]));

        $tagTrends12 = $this->getTagTrendsForPeriod(12, [], [ 'limit' => round($opts['limit'] / 2) ]);
        $tagTrends24 = $this->getTagTrendsForPeriod(24, array_map(function ($trend) {
            return $trend->getHashtag();
        }, $tagTrends12), [ 'limit' => round($opts['limit'] / 2) ]);

        return array_merge($tagTrends12, $tagTrends24);
    }

    /**
     * Get popular popular posts
     * @param array $tags
     * @param array $opts (optional)
     * @return Trend[]
     */
    public function getPostTrends(array $tags, array $opts = []): array
    {
        $opts = array_merge([
            'hoursAgo' => 12,
            'limit' => 5,
        ], $opts);

        $query = [
            'index' => 'minds_badger',
            'type' => 'activity,object:video',
            'body' =>  [
                'query' => [
                    'bool' => [
                        'must' => [
                            [
                                'range' => [
                                    '@timestamp' => [
                                        'gte' => strtotime("{$opts['hoursAgo']} hours ago") * 1000,
                                    ]
                                ],
                            ],
                            [
                                'terms' => [
                                    'tags' => $tags,
                                ]
                            ],
                        ]
                    ]
                ],
                'sort' => [ 'comments:count' => 'desc', ]
            ],
            'size' => $opts['limit'] * 2,
        ];

        $prepared = new ElasticSearch\Prepared\Search();
        $prepared->query($query);

        $response = $this->es->request($prepared);
        
        $trends = [];

        foreach ($response['hits']['hits'] as $doc) {
            $title = $doc['_source']['title'] ?: $doc['_source']['message'];

            shuffle($doc['_source']['tags']);
            $hashtag = $doc['_source']['tags'][0];

            $entity = $this->entitiesBuilder->single($doc['_id']);

            $exportedEntity = $entity->export();
            if (!$exportedEntity['thumbnail_src']) {
                continue;
            }

            $trend = new Trend();
            $trend->setGuid($doc['_id'])
                ->setTitle($title)
                ->setId($doc['_id'])
                ->setEntity($entity)
                ->setVolume($doc['_source']['comments:count'])
                ->setHashtag($hashtag);

            $trends[] = $trend;

            if (count($trends) >= $opts['limit']) {
                break;
            }
        }
        shuffle($trends);
        return $trends;
    }

    /**
     * @param int $hoursAgo
     * @param array $excludeTags
     * @param array $opts
     * @return Trend[]
     */
    protected function getTagTrendsForPeriod($hoursAgo, $excludeTags = [], array $opts = []): array
    {
        $opts = array_merge([
            'limit' => 10,
        ], $opts);

        $query = [
            'index' => 'minds_badger',
            'type' => 'activity',
            'body' =>  [
                'query' => [
                    'bool' => [
                        'must' => [
                            [
                                'range' => [
                                    '@timestamp' => [
                                        'gte' => strtotime("$hoursAgo hours ago") * 1000,
                                    ]
                                ],
                            ],
                            [
                                'terms' => [
                                    'tags' => $this->tagCloud,
                                ]
                            ],
                        ],
                        'must_not' => [
                            [
                                'terms' => [
                                    'nsfw' => [0,1,2,3,4,5,6]
                                ]
                            ],
                        ]
                    ],
                ],
                'aggs' => [
                    'tags' => [
                        'terms' => [
                            'field' => 'tags.keyword',
                            'min_doc_count' => 2,
                            'exclude' => $excludeTags,
                            'size' => $opts['limit'],
                            'order' => [
                                'tags_per_owner' => 'desc',
                            ],
                        ],
                        'aggs' => [
                            'tags_per_owner' => [
                                'cardinality' => [
                                    'field' => 'owner_guid.keyword',
                                ]
                            ]
                        ],
                    ]
                ]
            ],
            'size' => 0
        ];

        $prepared = new ElasticSearch\Prepared\Search();
        $prepared->query($query);

        $response = $this->es->request($prepared);

        $trends = [];
        
        foreach ($response['aggregations']['tags']['buckets'] as $bucket) {
            $tag = $bucket['key'];
            $trend = new Trend();
            $trend->setId("tag_$tag$period")
                ->setHashtag($tag)
                ->setVolume($bucket['doc_count']);
            $trends[] = $trend;
        }

        return $trends;
    }

    /**
     * Return entities for a search query and filter
     * @param string $query
     * @param string $filter
     * @return Response
     */
    public function getSearch(string $query, string $filter): Response
    {
        $algorithm = 'latest';
        $type = 'activity';

        switch ($filter) {
            case 'top':
                $algorithm = 'topV2';
                break;
            case 'channels':
                $type = 'user';
                break;
            case 'groups':
                $type = 'group';
                break;
        }

        $elasticEntities = new Core\Feeds\Elastic\Entities();
        $manager = Di::_()->get('Feeds\Elastic\Manager');
        $opts = [
            'cache_key' => Core\Session::getLoggedInUserGuid(),
            'access_id' => 2,
            'limit' => 5000,
            //'offset' => $offset,
            'nsfw' => [],
            'type' => $type,
            'algorithm' => $algorithm,
            'period' => '1y',
            'query' => $query,
        ];

        $rows = $manager->getList($opts);
        $entities = new Response();
        $entities = $entities->pushArray($rows->toArray());

        if ($type === 'user') {
            foreach ($entities as $entity) {
                $entity->getEntity()->exportCounts = true;
            }
        }

        return $entities;
    }

    /**
     * Returns the preferred and trending tags
     * @return array
     */
    public function getTags(): array
    {
        $tagsList = Di::_()->get('Hashtags\User\Manager')
            ->setUser(Session::getLoggedInUser())
            ->get([
                'defaults' => false,
                'trending' => true,
                'limit' => 20,
            ]);

        $tags = array_filter($tagsList, function ($tag) {
            return $tag['type'] === 'user';
        });

        $trending = array_filter($tagsList, function ($tag) {
            return $tag['type'] === 'trending';
        });

        return [
            'tags' => array_values($tags),
            'trending' => array_values($trending),
        ];
    }
}
