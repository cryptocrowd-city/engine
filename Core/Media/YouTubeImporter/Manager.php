<?php
/**
 * @author: eiennohi.
 */

namespace Minds\Core\Media\YouTubeImporter;

use Google_Client;
use Minds\Common\Repository\Response;
use Minds\Core\Config\Config;
use Minds\Core\Data\Call;
use Minds\Core\Di\Di;
use Minds\Core\Entities\Actions\Save;
use Minds\Core\Log\Logger;
use Minds\Core\Media\Assets\Video as VideoAssets;
use Minds\Core\Media\Repository as MediaRepository;
use Minds\Core\Media\YouTubeImporter\Delegates\EntityCreatorDelegate;
use Minds\Core\Media\YouTubeImporter\Delegates\QueueDelegate;
use Minds\Core\Media\YouTubeImporter\Exceptions\UnregisteredChannelException;
use Minds\Entities\EntitiesFactory;
use Minds\Entities\User;
use Minds\Entities\Video;

/**
 * YouTube Importer Manager
 * @package Minds\Core\Media\YouTubeImporter
 */
class Manager
{
    private const CACHE_KEY = 'youtube:token';

    // preferred qualities, in order of preference
    private const PREFERRED_QUALITIES = ['1080p', '720p', '360p', '240p', '144p'];

    /** @var Repository */
    protected $repository;

    /** @var MediaRepository */
    protected $mediaRepository;

    /** @var Google_Client */
    protected $client;

    /** @var Config */
    protected $config;

    /** @var QueueDelegate */
    protected $queueDelegate;

    /** @var EntityCreatorDelegate */
    protected $entityDelegate;

    /** @var Save */
    protected $save;

    /** @var Call */
    protected $call;

    /** @var VideoAssets */
    protected $videoAssets;

    /** @var EntitiesFactory */
    protected $entitiesBuilder;

    /** @var Logger */
    protected $logger;

    public function __construct(
        $repository = null,
        $mediaRepository = null,
        $client = null,
        $queueDelegate = null,
        $entityDelegate = null,
        $save = null,
        $call = null,
        $config = null,
        $assets = null,
        $entitiesBuilder = null,
        $logger = null
    ) {
        $this->repository = $repository ?: Di::_()->get('Media\YouTubeImporter\Repository');
        $this->mediaRepository = $mediaRepository ?: Di::_()->get('Media\Repository');
        $this->config = $config ?: Di::_()->get('Config');
        $this->queueDelegate = $queueDelegate ?: new QueueDelegate();
        $this->entityDelegate = $entityDelegate ?: new EntityCreatorDelegate();
        $this->save = $save ?: new Save();
        $this->call = $call ?: Di::_()->get('Database\Cassandra\Indexes');
        $this->client = $client ?: $this->buildClient();
        $this->videoAssets = $assets ?: new VideoAssets();
        $this->entitiesBuilder = $entitiesBuilder ?: Di::_()->get('EntitiesBuilder');
        $this->logger = $logger ?: Di::_()->get('Logger');
    }

    /**
     * Connects to a channel
     * @return string
     */
    public function connect(): string
    {
        $this->configClientAuth(false);
        return $this->client->createAuthUrl();
    }

    /**
     * Disconnects a YouTube account from a User
     * @param User $user
     * @param string $channelId
     * @return void
     * @throws \Minds\Exceptions\StopEventException
     */
    public function disconnect(User $user, string $channelId): void
    {
        // filter out the particular element, if found
        $channels = array_filter($user->getYouTubeChannels(), function ($value) use ($channelId) {
            return $value['id'] !== $channelId;
        });

        $user->setYouTubeChannels($channels);

        $this->save
            ->setEntity($user)
            ->save();
    }

    /**
     * Receives the access token and save to yt_connected
     * @param User $user
     * @param string $code
     */
    public function fetchToken(User $user, string $code): void
    {
        // We use the user's access token only this time to get channel details
        $this->configClientAuth(false);
        $this->client->fetchAccessTokenWithAuthCode($code);

        $youtube = new \Google_Service_YouTube($this->client);

        $channelsResponse = $youtube->channels->listChannels('id, snippet', [
            'mine' => 'true',
        ]);

        $channels = $user->getYouTubeChannels();
        foreach ($channelsResponse['items'] as $channel) {
            // only add the channel if it's not already registered
            if (count(array_filter($channels, function ($value) use ($channel) {
                return $value['id'] === $channel['id'];
            })) === 0) {
                $channels[] = [
                    'id' => $channel['id'],
                    'title' => $channel['snippet']['title'],
                    'connected' => time(),
                ];
            }
        }

        // get channel ids
        $channelIds = array_map(function ($item) {
            return $item['id'];
        }, $channels);

        // save channel ids into indexes
        foreach ($channelIds as $id) {
            $this->call->insert("yt_channel:user:{$id}", [$user->getGUID()]);
        }

        $user->setYouTubeChannels($channels)
            ->save();
    }

    /**
     * @param array $opts
     * @return Response
     * @throws \Exception
     * @throws UnregisteredChannelException
     */
    public function getVideos(array $opts): Response
    {
        $opts = array_merge([
            'limit' => 12,
            'offset' => 0,
            'user_guid' => null,
            'youtube_id' => null,
            'youtube_channel_id' => null,
            'status' => null,
            'time_created' => [
                'lt' => null,
                'gt' => null,
            ],
        ], $opts);

        if (!$this->validateChannel($opts['user'], $opts['youtube_channel_id'])) {
            throw new UnregisteredChannelException();
        }

        // if status is 'queued' or 'completed', then we don't consult youtube
        if (isset($opts['status']) && in_array($opts['status'], ['queued', 'completed'], true)) {
            return $this->repository->getList($opts);
        }

        $this->configClientAuth(true);

        $youtube = new \Google_Service_YouTube($this->client);

        // get channel
        $channelsResponse = $youtube->channels->listChannels('contentDetails', [
            'id' => $opts['youtube_channel_id'],
        ]);

        $videos = new Response();

        foreach ($channelsResponse['items'] as $channel) {
            $uploadsListId = $channel['contentDetails']['relatedPlaylists']['uploads'];

            // get videos
            $playlistResponse = $youtube->playlistItems->listPlaylistItems('snippet', [
                'playlistId' => $uploadsListId,
                'maxResults' => 50,
            ]);

            $videos->setPagingToken($playlistResponse->getNextPageToken());

            // get all IDs so we can do a single query call
            $videoIds = array_map(function ($item) {
                return $item['snippet']['resourceId']['videoId'];
            }, $playlistResponse['items']);

            // get data on all returned videos
            $videoResponse = $youtube->videos->listVideos('contentDetails,statistics', ['id' => implode(',', $videoIds)]);

            // build video entities
            foreach ($playlistResponse['items'] as $item) {
                $youtubeId = $item['snippet']['resourceId']['videoId'];
                $videoData = array_filter($videoResponse['items'], function ($item) use ($youtubeId) {
                    return $item['id'] === $youtubeId;
                })[0];

                // try to find it in our db
                $response = $this->repository->getList([
                    'youtube_id' => $youtubeId,
                    'limit' => 1,
                ])->toArray();

                $ytVideo = (new YTVideo())
                    ->setYoutubeCreationDate(strtotime($item['snippet']['publishedAt']))
                    ->setDuration($this->parseISO8601($videoResponse['items'][0]['contentDetails']['duration']))
                    ->setLikes($videoData['statistics']['likeCount'])
                    ->setDislikes($videoData['statistics']['dislikeCount'])
                    ->setFavorites($videoData['statistics']['favoriteCount'])
                    ->setViews($videoData['statistics']['viewCount']);

                if (count($response) > 0) {
                    /** @var Video $video */
                    $video = $response[0];

                    $ytVideo
                        ->setEntity($video)
                        ->setOwnerGuid($video->owner_guid)
                        ->setOwner($video->getOwnerEntity())
                        ->setStatus($video->getTranscodingStatus())
                        ->setThumbnail($video->getIconUrl())
                        ->setVideoId($video->getYoutubeId())
                        ->setChannelId($video->getYoutubeChannelId())
                        ->setTitle($video->getTitle())
                        ->setDescription($video->getDescription());
                } else {
                    $thumbnail = $this->config->get('cdn_url') . 'api/v2/media/proxy?src=' . urlencode($item['snippet']['thumbnails']->getHigh()['url']);

                    $ytVideo
                        ->setVideoId($item['snippet']['resourceId']['videoId'])
                        ->setChannelId($item['snippet']['channelId'])
                        ->setDescription($item['snippet']['description'])
                        ->setTitle($item['snippet']['title'])
                        ->setThumbnail($thumbnail);
                }
                $videos[] = $ytVideo;
            }
        }
        return $videos;
    }

    /**
     * Initiates video import (uses Repository - queues for transcoding)
     * @param YTVideo $ytVideo
     * @throws \IOException
     * @throws \InvalidParameterException
     * @throws UnregisteredChannelException
     */
    public function import(YTVideo $ytVideo): void
    {
        if (!$this->validateChannel($ytVideo->getOwner(), $ytVideo->getChannelId())) {
            throw new UnregisteredChannelException();
        }
        // get and decode the data
        parse_str(file_get_contents("https://youtube.com/get_video_info?video_id=" . $ytVideo->getVideoId()), $info);

        $videoData = json_decode($info['player_response'], true);

        // get video details
        $videoDetails = $videoData['videoDetails'];

        // get streaming formats
        $streamingDataFormats = $videoData['streamingData']['formats'];

        // validate length
        $this->videoAssets->validate(['length' => $videoDetails['lengthSeconds'] / 60]);

        // find best suitable format
        $format = [];
        $i = 0;

        $length = count(static::PREFERRED_QUALITIES);
        while (count($format) === 0 && $i < $length) {
            foreach ($streamingDataFormats as $f) {
                if ($f['qualityLabel'] === static::PREFERRED_QUALITIES[$i]) {
                    $format = $f;
                }
            }

            $i++;
        }

        // create the video
        $video = new Video();

        $video->patch([
            'title' => isset($videoDetails['title']) ? $videoDetails['title'] : '',
            'description' => isset($videoDetails['description']) ? $videoDetails['description'] : '',
            'batch_guid' => 0,
            'access_id' => 0,
            'owner_guid' => $ytVideo->getOwnerGuid(),
            'full_hd' => $ytVideo->getOwner()->isPro(),
            'youtube_id' => $ytVideo->getVideoId(),
            'youtube_channel_id' => $ytVideo->getChannelId(),
            'transcoding_status' => 'queued',
            'chosen_format_url' => $format['url'],
        ]);

        // check if we're below the threshold
        if ($this->getOwnersEligibility([$ytVideo->getOwner()->guid])[$ytVideo->getOwner()->guid] < $this->getThreshold()) {
            $this->queue($video);
        }
    }

    /**
     * Sends a video to a queue to be transcoded
     * @param Video $video
     */
    public function queue(Video $video): void
    {
        // send to queue so it gets downloaded
        $this->queueDelegate->onAdd($video);
    }

    /**
     * Returns maximum daily imports per user
     * @return int
     */
    public function getThreshold()
    {
        return $this->config->get('google')['youtube']['max_daily_imports'];
    }

    /**
     * Downloads a video, triggers the transcode and creates an activity.
     * Gets called by the queue runner.
     * @param Video $video
     * @throws \Minds\Exceptions\StopEventException
     */
    public function onQueue(Video $video): void
    {
        $this->logger->info("[YouTubeImporter] Downloading YouTube video ({$video->getYoutubeId()}) \n");

        // download the file
        $file = tmpfile();
        $path = stream_get_meta_data($file)['uri'];
        file_put_contents($path, fopen($video->getChosenFormatUrl(), 'r'));

        $this->logger->info("[YouTubeImporter] File saved \n");

        $media = [
            'file' => $path,
        ];

        $this->videoAssets
            ->setEntity($video)
            ->validate($media);

        $this->logger->info("[YouTubeImporter] Initiating upload to S3 ({$video->guid}) \n");

        $video->setAssets($this->videoAssets->upload($media, []));

        $this->logger->info("[YouTubeImporter] Saving video ({$video->guid}) \n");

        $success = $this->save
            ->setEntity($video)
            ->save(true);

        if (!$success) {
            throw new \Exception('Error saving video');
        }

        // create activity
        $this->entityDelegate->createActivity($video);
    }

    /**
     * @param User $user
     * @param string $videoId
     * @return bool
     * @throws \Exception
     */
    public function cancel(User $user, string $videoId): bool
    {
        $response = $this->repository->getList([
            'youtube_id' => $videoId,
            'limit' => 1,
        ])->toArray();

        $deleted = false;
        if (count($response) > 0 && $response[0]->getOwnerGUID() === $user->getGUID()) {
            $deleted = $this->mediaRepository->delete($response[0]->getGUID());
        }

        return $deleted;
    }

    /**
     * (Un)Subscribes from YouTube's push notifications
     * @param User $user
     * @param string $channelId
     * @param bool $subscribe
     * @return bool returns true if it succeeds
     * @throws UnregisteredChannelException
     */
    public function updateSubscription(User $user, string $channelId, bool $subscribe): bool
    {
        if (!$this->validateChannel($user, $channelId)) {
            throw new UnregisteredChannelException();
        }

        $subscribeUrl = 'https://pubsubhubbub.appspot.com/subscribe';
        $topicUrl = "https://www.youtube.com/xml/feeds/videos.xml?channel_id={$channelId}";
        $callbackUrl = $this->config->get('site_url') . 'api/v3/media/youtube-importer/hook';

        $data = [
            'hub.mode' => $subscribe ? 'subscribe' : 'unsubscribe',
            'hub.callback' => $callbackUrl,
            'hub.lease_seconds' => 60 * 60 * 24 * 365,
            'hub.topic' => $topicUrl,
        ];

        $opts = ['http' =>
            [
                'method' => 'POST',
                'header' => 'Content-type: application/x-www-form-urlencoded',
                'content' => http_build_query($data),
            ],
        ];

        $context = stream_context_create($opts);

        @file_get_contents($subscribeUrl, false, $context);

        $this->logger->info("[YouTubeImporter] " . $http_response_header);

        return preg_match('200', $http_response_header[0]) === 1;
    }

    /**
     * Imports a newly added YT video. This is called when the hook receives a new update.
     * @param YTVideo $video
     * @throws \IOException
     * @throws \InvalidParameterException
     */
    public function receiveNewVideo(YTVideo $video): void
    {
        // see if we have a video like this already saved
        $response = $this->repository->getList(['youtube_id' => $video->getVideoId()]);

        // if the video isn't there, we'll download it
        if ($response->count() === 0) {
            // fetch User associated with this channelId
            $result = $this->call->getRow("yt_channel:user:{$video->getChannelId()}");

            if (count($result) === 0) {
                // no User is associated with this youtube channel
                return;
            }

            /** @var User $user */
            $user = $this->entitiesBuilder->single($result[$video->getChannelId()]);

            if ($user->isBanned() || $user->getDeleted()) {
                return;
            }

            $video->setOwner($user);

            $this->import($video);
        }
    }

    /**
     * Returns an associative array :guid => :times, where :times is the amount of transcodes for that user
     * in the last 24 hours
     * @param array $ownerGuids
     * @return array
     */
    public function getOwnersEligibility(array $ownerGuids): array
    {
        return $this->repository->getOwnersEligibility($ownerGuids);
    }

    /**
     * Creates new instance of Google_Client and adds client_id and secret
     * @param bool $useDevKey
     * @return Google_Client
     */
    private function buildClient(bool $useDevKey = true): Google_Client
    {
        $client = new Google_Client();

        // add scopes
        $client->addScope(\Google_Service_YouTube::YOUTUBE_READONLY);

        $client->setRedirectUri($this->config->get('site_url')
            . 'api/v3/media/youtube-importer/account/redirect');

        $client->setAccessType('offline');

        return $client;
    }

    /**
     * Configures the Google Client to either use a developer key or a client id / secret
     * @param $useDevKey
     */
    private function configClientAuth($useDevKey)
    {
        // set auth config
        if ($useDevKey) {
            $this->client->setDeveloperKey($this->config->get('google')['youtube']['api_key']);
            $this->client->setClientId('');
            $this->client->setClientSecret('');
        } else {
            $this->client->setDeveloperKey('');
            $this->client->setClientId($this->config->get('google')['youtube']['client_id']);
            $this->client->setClientSecret($this->config->get('google')['youtube']['client_secret']);
        }
    }

    /**
     * Returns whether the channel belongs to the User
     * @param User $user
     * @param string $channelId
     * @return bool
     */
    private function validateChannel(User $user, string $channelId): bool
    {
        return count(array_filter($user->getYouTubeChannels(), function ($value) use ($channelId) {
            return $value['id'] === $channelId;
        })) !== 0;
    }

    /**
     * returns duration in seconds
     * @param string $duration
     * @return int
     */
    private function parseISO8601(string $duration): int
    {
        return (new \DateTime('@0'))->add(new \DateInterval($duration))->getTimestamp();
    }
}
