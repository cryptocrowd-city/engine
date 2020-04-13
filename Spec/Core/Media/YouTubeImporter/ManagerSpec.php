<?php

namespace Spec\Minds\Core\Media\YouTubeImporter;

use Minds\Common\Repository\Response;
use Minds\Core\Config\Config;
use Minds\Core\Data\cache\abstractCacher;
use Minds\Core\Data\Call;
use Minds\Core\Entities\Actions\Save;
use Minds\Core\Log\Logger;
use Minds\Core\Media\YouTubeImporter\Delegates\EntityCreatorDelegate;
use Minds\Core\Media\YouTubeImporter\Delegates\QueueDelegate;
use Minds\Core\Media\YouTubeImporter\Manager;
use Minds\Core\Media\YouTubeImporter\Repository;
use Minds\Core\Media\YouTubeImporter\YTVideo;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ManagerSpec extends ObjectBehavior
{
    /** @var Repository */
    protected $repository;

    /** @var \Google_Client */
    protected $client;

    /** @var Config */
    protected $config;

    /** @var abstractCacher */
    protected $cacher;

    /** @var QueueDelegate */
    protected $queueDelegate;

    /** @var EntityCreatorDelegate */
    protected $entityDelegate;

    /** @var Save */
    protected $save;

    /** @var Call */
    protected $call;

    /** @var Logger */
    protected $logger;

    public function let(Repository $repository, \Google_Client $client, QueueDelegate $queueDelegate, EntityCreatorDelegate $entityDelegate, Save $save, abstractCacher $cacher, Config $config, Call $call, Logger $logger)
    {
        $this->repository = $repository;
        $this->config = $config;
        $this->cacher = $cacher;
        $this->queueDelegate = $queueDelegate;
        $this->entityDelegate = $entityDelegate;
        $this->save = $save;
        $this->call = $call;
        $this->logger = $logger;
        $this->client = $client;

        $this->beConstructedWith($repository, $client, $queueDelegate, $entityDelegate, $save, $cacher, $call, $config, $logger);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(Manager::class);
    }

    public function it_should_return_auth_url()
    {
        $this->client->createAuthUrl()
            ->shouldBeCalled()
            ->willReturn('url');
        $this->connect()->shouldReturn('url');
    }

    public function it_should_get_completed_videos(Response $response)
    {
        $this->repository->getList(Argument::any())
            ->shouldBeCalled()
            ->willReturn($response);

        $this->getVideos([
            'status' => 'completed',
        ])
            ->shouldReturn($response);
    }

    public function it_should_receive_a_new_video(YTVideo $video)
    {
        $video->getVideoId()
            ->shouldBeCalled()
            ->willReturn('id');

        $video->getChannelId()
            ->shouldBeCalled()
            ->willReturn('channel_id');

        $this->repository->getList(['youtube_id' => 'id'])
            ->shouldBeCalled()
            ->willReturn(new Response());

        $this->call->getRow('yt_channel:user:channel_id');

        $this->receiveNewVideo($video);
    }

    public function it_should_get_owners_elegibility()
    {
        $this->repository->getOwnersEligibility([1, 2])
            ->shouldBeCalled()
            ->willReturn([1 => 10, 2 => 3]);

        $this->getOwnersEligibility([1, 2])->shouldReturn([1 => 10, 2 => 3]);
    }

    public function it_should_get_the_daily_threshold()
    {
        $this->config->get('google')
            ->shouldBeCalled()
            ->willReturn([
                'youtube' => [
                    'max_daily_imports' => 10,
                ],
            ]);

        $this->getThreshold()->shouldReturn(10);
    }
}
