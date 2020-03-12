<?php

namespace Minds\Controllers\Cli;

use Minds\Core;
use Minds\Cli;
use Minds\Interfaces;
use Minds\Exceptions;
use Minds\Exceptions\ProvisionException;
use Minds\Core\Channels\Snapshots\Repository;
use Minds\Core\Channels\Snapshots\Snapshot;
use Minds\Core\Comments\Comment;
use Minds\Core\Comments\Manager as CommentManager;
use Minds\Core\Data\ElasticSearch\Client as ElasticSearchClient;
use Minds\Core\Data\ElasticSearch\Prepared\Search;
use Minds\Core\Di\Di;

class Test extends Cli\Controller implements Interfaces\CliControllerInterface
{
    public function __construct()
    {
        define('__MINDS_INSTALLING__', true);
    }

    public function help($command = null)
    {
        $this->out('TBD');
    }

    public function exec()
    {
        $luids = [
            'eyJfdHlwZSI6ImNvbW1lbnQiLCJjaGlsZF9wYXRoIjoiMTA4MjY1NjM5NDU2NzQ4NzQ5NjoxMDgyNjc4OTMzMTgwMTI1MjAyOjAiLCJlbnRpdHlfZ3VpZCI6IjEwNzY5MjMxOTU1NTE5MTE5NDIiLCJndWlkIjoiMTA4MjY3ODkzMzE4MDEyNTIwMiIsInBhcmVudF9wYXRoIjoiMDowOjAiLCJwYXJ0aXRpb25fcGF0aCI6IjEwODI2NTYzOTQ1Njc0ODc0OTY6MDowIn0=',
            'eyJfdHlwZSI6ImNvbW1lbnQiLCJjaGlsZF9wYXRoIjoiMTA4MjY1NjM5NDU2NzQ4NzQ5NjowOjAiLCJlbnRpdHlfZ3VpZCI6IjEwNzY5MjMxOTU1NTE5MTE5NDIiLCJndWlkIjoiMTA4MjY1NjM5NDU2NzQ4NzQ5NiIsInBhcmVudF9wYXRoIjoiMDowOjAiLCJwYXJ0aXRpb25fcGF0aCI6IjA6MDowIn0=',
            'eyJfdHlwZSI6ImNvbW1lbnQiLCJjaGlsZF9wYXRoIjoiMTA4MjY1NjM5NDU2NzQ4NzQ5NjoxMDgyNjgwNDY4NDg4MzI3MTc1OjAiLCJlbnRpdHlfZ3VpZCI6IjEwNzY5MjMxOTU1NTE5MTE5NDIiLCJndWlkIjoiMTA4MjY4MDQ2ODQ4ODMyNzE3NSIsInBhcmVudF9wYXRoIjoiMDowOjAiLCJwYXJ0aXRpb25fcGF0aCI6IjEwODI2NTYzOTQ1Njc0ODc0OTY6MDowIn0=',
            'eyJfdHlwZSI6ImNvbW1lbnQiLCJjaGlsZF9wYXRoIjoiMTA4MjY1NjM5NDU2NzQ4NzQ5NjoxMDgyNjgwNjg0NzU5MjI0MzM3OjAiLCJlbnRpdHlfZ3VpZCI6IjEwNzY5MjMxOTU1NTE5MTE5NDIiLCJndWlkIjoiMTA4MjY4MDY4NDc1OTIyNDMzNyIsInBhcmVudF9wYXRoIjoiMDowOjAiLCJwYXJ0aXRpb25fcGF0aCI6IjEwODI2NTYzOTQ1Njc0ODc0OTY6MDowIn0=',
            'eyJfdHlwZSI6ImNvbW1lbnQiLCJjaGlsZF9wYXRoIjoiMTA4MjY1NjMxODA4MDE1OTc1NTowOjAiLCJlbnRpdHlfZ3VpZCI6IjEwNzY5MjMxOTU1NTE5MTE5NDIiLCJndWlkIjoiMTA4MjY1NjMxODA4MDE1OTc1NSIsInBhcmVudF9wYXRoIjoiMDowOjAiLCJwYXJ0aXRpb25fcGF0aCI6IjA6MDowIn0=',
            'eyJfdHlwZSI6ImNvbW1lbnQiLCJjaGlsZF9wYXRoIjoiMTA4MjY1NjM5NDU2NzQ4NzQ5NjoxMDgyNjc5MjI1NTMxNTAyNjA2OjAiLCJlbnRpdHlfZ3VpZCI6IjEwNzY5MjMxOTU1NTE5MTE5NDIiLCJndWlkIjoiMTA4MjY3OTIyNTUzMTUwMjYwNiIsInBhcmVudF9wYXRoIjoiMDowOjAiLCJwYXJ0aXRpb25fcGF0aCI6IjEwODI2NTYzOTQ1Njc0ODc0OTY6MDowIn0=',
            'eyJfdHlwZSI6ImNvbW1lbnQiLCJjaGlsZF9wYXRoIjoiMTA4MjY1NjM5NDU2NzQ4NzQ5NjoxMDgyNjY5NDc3NDE1MzU4NDg0OjAiLCJlbnRpdHlfZ3VpZCI6IjEwNzY5MjMxOTU1NTE5MTE5NDIiLCJndWlkIjoiMTA4MjY2OTQ3NzQxNTM1ODQ4NCIsInBhcmVudF9wYXRoIjoiMDowOjAiLCJwYXJ0aXRpb25fcGF0aCI6IjEwODI2NTYzOTQ1Njc0ODc0OTY6MDowIn0=',
            'eyJfdHlwZSI6ImNvbW1lbnQiLCJjaGlsZF9wYXRoIjoiMTA4MjY1NjM5NDU2NzQ4NzQ5NjoxMDgyNjU2NDMwMjczNTk3NDQ0OjAiLCJlbnRpdHlfZ3VpZCI6IjEwNzY5MjMxOTU1NTE5MTE5NDIiLCJndWlkIjoiMTA4MjY1NjQzMDI3MzU5NzQ0NCIsInBhcmVudF9wYXRoIjoiMDowOjAiLCJwYXJ0aXRpb25fcGF0aCI6IjEwODI2NTYzOTQ1Njc0ODc0OTY6MDowIn0=',
            'eyJfdHlwZSI6ImNvbW1lbnQiLCJjaGlsZF9wYXRoIjoiMTA4MjY4MDYyMDQzOTU3MjQ4MjowOjAiLCJlbnRpdHlfZ3VpZCI6IjEwNzY5MjMxOTU1NTE5MTE5NDIiLCJndWlkIjoiMTA4MjY4MDYyMDQzOTU3MjQ4MiIsInBhcmVudF9wYXRoIjoiMDowOjAiLCJwYXJ0aXRpb25fcGF0aCI6IjA6MDowIn0=',
            // lvl 1 reply
            'eyJfdHlwZSI6ImNvbW1lbnQiLCJjaGlsZF9wYXRoIjoiMTA4MjY1NjM5NDU2NzQ4NzQ5NjoxMDgyNjgxNjc4OTcyODUwMTk1OjAiLCJlbnRpdHlfZ3VpZCI6IjEwNzY5MjMxOTU1NTE5MTE5NDIiLCJndWlkIjoiMTA4MjY4MTY3ODk3Mjg1MDE5NSIsInBhcmVudF9wYXRoIjoiMDowOjAiLCJwYXJ0aXRpb25fcGF0aCI6IjEwODI2NTYzOTQ1Njc0ODc0OTY6MDowIn0=',
            // root lvl
            'eyJfdHlwZSI6ImNvbW1lbnQiLCJjaGlsZF9wYXRoIjoiMTA4MjY4MjMxNjUxNTQ0Njc5NzowOjAiLCJlbnRpdHlfZ3VpZCI6IjEwNzY5MjMxOTU1NTE5MTE5NDIiLCJndWlkIjoiMTA4MjY4MjMxNjUxNTQ0Njc5NyIsInBhcmVudF9wYXRoIjoiMDowOjAiLCJwYXJ0aXRpb25fcGF0aCI6IjA6MDowIn0=',
        ];

        $es = Di::_()->get('Database\ElasticSearch');
        $commentManager = new CommentManager();

        //                foreach($luids as $luid) {
        //                    $comment = $commentManager->getByLuid($luid);
        //                    if (!$comment) {
        //                        continue;
        //                    }
        //
        //                    var_dump($comment->getBody());
        //                }
        //                return;

        $minds = '1074394696509296657';
        $minds_banned1 = '1082656111057702914';
        $minds_banned2 = '1083735671736111114';

        $result = null;
        $keys = [];
        $from = 0;

//        do {


        $body = [
                'query' => [
                    'bool' => [
                        'must' => [
                            [
                                'term' => [
                                    'user_guid.keyword' => $minds_banned2,
                                ],
                            ],
                            [
                                'term' => [
                                    'action' => 'comment',
                                ],
                            ],
                        ],
                    ],
                ],
                //            'aggs' => [
                //                'comment_luids' => [
                //                    'terms' => [
                //                        'field' => 'comment_guid.keyword',
                //                        'size' => 500000,
                //                    ],
                //                ],
                //            ],
            ];

        $query = [
                'body' => $body,
                'size' => 1000,
                'from' => $from,
                'index' => 'minds-metrics-*',
                'type' => 'action',
            ];

        $prepared = new Search();
        $prepared->query($query);

        try {
            $result = $es->request($prepared);
        } catch (\Exception $e) {
            error_log((string) $e);
            throw $e; // Re-throw
        }

        //        var_dump($result);
        //        return;

        //        foreach ($result['aggregations']['comment_luids']['buckets'] as $row) {
        //            $keys[] = $row['key'];
        //        }

        foreach ($result['hits']['hits'] as $row) {
            $keys[] = $row['_source']['comment_guid'];
            $from++;
        }
//        } while ($result && $result['hits'] && $result['hits']['hits']);
        foreach ($keys as $commentLuid) {
            $comment = $commentManager->getByLuid($commentLuid);
            if (!$comment) {
                continue;
            }

            var_dump($comment->getBody());
//            $commentManager->delete($comment, [
//                'force' => true,
//            ]);
        }
    }

    private function getTrendingActivities()
    {
        $result = Core\Di\Di::_()->get('Trending\Repository')->getList([
            'type' => 'newsfeed',
            'limit' => 12,
        ]);
        ksort($result['guids']);
        $options['guids'] = $result['guids'];

        $activities = Core\Entities::get(array_merge([
            'type' => 'activity',
        ], $options));

        $activities = array_filter($activities, function ($activity) {
            if ($activity->paywall) {
                return false;
            }

            if ($activity->remind_object && $activity->remind_object['paywall']) {
                return false;
            }

            return true;
        });

        return $activities;
    }
}
