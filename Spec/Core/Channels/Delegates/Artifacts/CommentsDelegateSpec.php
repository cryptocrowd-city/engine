<?php

namespace Spec\Minds\Core\Channels\Delegates\Artifacts;

use Minds\Core\Channels\Delegates\Artifacts\CommentsDelegate;
use Minds\Core\Channels\Snapshots\Repository;
use Minds\Core\Channels\Snapshots\Snapshot;
use Minds\Core\Comments\Comment;
use Minds\Core\Comments\Manager as CommentManager;
use Minds\Core\Data\ElasticSearch\Client as ElasticSearchClient;
use Minds\Core\Data\ElasticSearch\Prepared\Search as PreparedSearch;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class CommentsDelegateSpec extends ObjectBehavior
{
    /** @var Repository */
    protected $repository;

    /** @var ElasticSearchClient */
    protected $elasticsearch;

    /** @var CommentManager */
    protected $commentManager;

    public function let(
        Repository $repository,
        ElasticSearchClient $elasticsearch,
        CommentManager $commentManager
    ) {
        $this->beConstructedWith($repository, $elasticsearch, $commentManager);
        $this->repository = $repository;
        $this->elasticsearch = $elasticsearch;
        $this->commentManager = $commentManager;
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(CommentsDelegate::class);
    }

    public function it_should_snapshot(
    ) {
        $this->elasticsearch->request(Argument::that(function (PreparedSearch $search) {
            $query = $search->build();

            return $query['index'] === 'minds-metrics-*';
        }))
            ->shouldBeCalled()
            ->willReturn(
                [
                    'aggregations' => [
                        'comment_luids' => [
                            'buckets' => [
                                ['key' => 'a0000001'],
                                ['key' => 'a0000002'],
                            ],
                        ],
                    ],
                ]
            );

        $this->commentManager->getByLuid('a0000001')
            ->shouldBeCalled()
            ->willReturn((new Comment()));

        $this->commentManager->getByLuid('a0000002')
            ->shouldBeCalled()
            ->willReturn((new Comment()));

        $this->repository->add(Argument::that(function (Snapshot $snapshot) {
            $comment = new Comment();
            return $snapshot->getJsonData() === ['comment' => serialize($comment)];
        }))
            ->shouldBeCalledTimes(2)
            ->willReturn(true);

        $this
            ->snapshot(1000)
            ->shouldReturn(true);
    }

    public function it_should_restore(
        Snapshot $snapshotMock
    ) {
        $this->repository->getList([
            'user_guid' => 1000,
            'type' => 'comments',
        ])
            ->shouldBeCalled()
            ->willReturn([
                $snapshotMock,
                $snapshotMock,
            ]);

        $snapshotMock->getJsonData()
            ->shouldBeCalledTimes(2)
            ->willReturn(['comment' => serialize(new Comment())]);

        $this->commentManager->restore(Argument::type(Comment::class))
            ->shouldBeCalledTimes(2)
            ->willReturn(true);

        $this
            ->restore(1000)
            ->shouldReturn(true);
    }

    public function it_should_hide(
        Comment $commentMock
    ) {
        $this->elasticsearch->request(Argument::that(function (PreparedSearch $search) {
            $query = $search->build();


            return $query['index'] === 'minds-metrics-*';
        }))
            ->shouldBeCalled()
            ->willReturn(
                [
                    'aggregations' => [
                        'comment_luids' => [
                            'buckets' => [
                                ['key' => 'a0000001'],
                                ['key' => 'a0000002'],
                            ],
                        ],
                    ],
                ]
            );

        $this->commentManager->getByLuid('a0000001')
            ->shouldBeCalled()
            ->willReturn($commentMock);

        $this->commentManager->getByLuid('a0000002')
            ->shouldBeCalled()
            ->willReturn($commentMock);

        $this->commentManager->delete($commentMock, [ 'force' => true ])
            ->shouldBeCalledTimes(2)
            ->willReturn(true);

        $this
            ->hide(1000)
            ->shouldReturn(true);
    }

    public function it_should_delete(
        Comment $commentMock
    ) {
        $this->elasticsearch->request(Argument::that(function (PreparedSearch $search) {
            $query = $search->build();


            return $query['index'] === 'minds-metrics-*';
        }))
            ->shouldBeCalled()
            ->willReturn(
                [
                    'aggregations' => [
                        'comment_luids' => [
                            'buckets' => [
                                ['key' => 'a0000001'],
                                ['key' => 'a0000002'],
                            ],
                        ],
                    ],
                ]
            );

        $this->commentManager->getByLuid('a0000001')
            ->shouldBeCalled()
            ->willReturn($commentMock);

        $this->commentManager->getByLuid('a0000002')
            ->shouldBeCalled()
            ->willReturn($commentMock);

        $this->commentManager->delete($commentMock, [ 'force' => true ])
            ->shouldBeCalledTimes(2)
            ->willReturn(true);

        $this
            ->delete(1000)
            ->shouldReturn(true);
    }
}
