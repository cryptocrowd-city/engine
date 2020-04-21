<?php
/**
 * @author: eiennohi.
 */

namespace Minds\Core\Media\YouTubeImporter;

use Google_Client;
use Minds\Common\Repository\Response;
use Minds\Core\Config\Config;
use Minds\Core\Data\cache\abstractCacher;
use Minds\Core\Data\Call;
use Minds\Core\Di\Di;
use Minds\Core\Entities\Actions\Save;
use Minds\Core\Log\Logger;
use Minds\Core\Media\Assets\Video as VideoAssets;
use Minds\Core\Media\YouTubeImporter\Delegates\EntityCreatorDelegate;
use Minds\Core\Media\YouTubeImporter\Delegates\QueueDelegate;
use Minds\Core\Media\YouTubeImporter\Exceptions\UnregisteredChannelException;
use Minds\Entities\EntitiesFactory;
use Minds\Entities\User;
use Minds\Entities\Video;
use Pubsubhubbub\Subscriber\Subscriber;
use Zend\Diactoros\Response\JsonResponse;

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

    /** @var Google_Client */
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

    /** @var VideoAssets */
    protected $videoAssets;

    /** @var EntitiesFactory */
    protected $entitiesBuilder;

    /** @var Subscriber */
    protected $subscriber;

    /** @var Logger */
    protected $logger;

    public function __construct(
        $repository = null,
        $client = null,
        $queueDelegate = null,
        $entityDelegate = null,
        $save = null,
        $cacher = null,
        $call = null,
        $config = null,
        $assets = null,
        $entitiesBuilder = null,
        $subscriber = null,
        $logger = null
    )
    {
        $this->repository = $repository ?: Di::_()->get('Media\YouTubeImporter\Repository');
        $this->config = $config ?: Di::_()->get('Config');
        $this->cacher = $cacher ?: Di::_()->get('Cache');
        $this->queueDelegate = $queueDelegate ?: new QueueDelegate();
        $this->entityDelegate = $entityDelegate ?: new EntityCreatorDelegate();
        $this->save = $save ?: new Save();
        $this->call = $call ?: Di::_()->get('Database\Cassandra\Indexes');
        $this->client = $client ?: $this->buildClient();
        $this->videoAssets = $assets ?: new VideoAssets();
        $this->entitiesBuilder = $entitiesBuilder ?: Di::_()->get('EntitiesBuilder');
        $this->subscriber = $subscriber ?: new Subscriber('https://pubsubhubbub.appspot.com/subscribe', $this->config->get('site_url') . 'api/v3/media/youtube-importer/hook');
        $this->logger = $logger ?: Di::_()->get('Logger');
    }

    /**
     * Connects to a channel
     * @param bool $getMindsToken
     * @return string
     */
    public function connect(bool $getMindsToken = false): string
    {
        if ($getMindsToken) {
            $this->client->setRedirectUri($this->config->get('site_url')
                . 'api/v3/media/youtube-importer/account/redirect?update_minds_token=true');
        }
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
     * @param bool $updateMindsToken
     */
    public function fetchToken(User $user, string $code, bool $updateMindsToken): void
    {
        $token = $this->client->fetchAccessTokenWithAuthCode($code);

        if ($updateMindsToken) {
            $this->cacher->set(self::CACHE_KEY, $token);
            return;
        }

        $youtube = new \Google_Service_YouTube($this->client);

        // We use the user's access token only this time to get channel details
        $channelsResponse = $youtube->channels->listChannels('id, snippet', [
            'mine' => 'true',
        ]);

        // TODO: refactor this into a delegate
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
                    'auto_import' => false,
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

        // Use Minds' access token
        $this->client->setAccessToken($this->cacher->get(self::CACHE_KEY));

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

            foreach ($playlistResponse['items'] as $item) {
                $youtubeId = $item['snippet']['resourceId']['videoId'];

                // try to find it in our db
                $response = $this->repository->getList([
                    'youtube_id' => $youtubeId,
                    'limit' => 1,
                ])->toArray();

                $ytVideo = new YTVideo();
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

        $topicUrl = "https://www.youtube.com/xml/feeds/videos.xml?channel_id={$channelId}";

        // update the channel if the value changed
        $channels = $user->getYouTubeChannels();
        $i = 0;
        $found = false;
        $updated = false;
        while (!$found && $i < count($channels)) {
            if ($channels[$i]['id'] === $channelId) {
                $found = true;
                if ($channels[$i]['auto_import'] === $subscribe) {
                    return true;
                }
                $updated = $subscribe ? $this->subscriber->subscribe($topicUrl) !== false : $this->subscriber->unsubscribe($topicUrl) !== false;

                // if the subscription was correctly updated
                if ($updated) {
                    // update and save channel
                    $channels[$i]['auto_import'] = $subscribe;

                    $user->setYouTubeChannels($channels);

                    $this->save
                        ->setEntity($user)
                        ->save();
                }
            }
            $i++;
        }

        return $updated;
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
     * @return Google_Client
     */
    private function buildClient(): Google_Client
    {
        $client = new Google_Client();
        // set auth config
        $client->setClientId($this->config->get('google')['youtube']['client_id']);
        $client->setClientSecret($this->config->get('google')['youtube']['client_secret']);

        // add scopes
        $client->addScope(\Google_Service_YouTube::YOUTUBE_READONLY);

        // TODO redirect URI should be to our youtube importer page for better UX
        // add redirect URI
        $client->setRedirectUri($this->config->get('site_url')
            . 'api/v3/media/youtube-importer/account/redirect');

        $client->setAccessType('offline');

        // cache this
        //        $token = $this->config->get('google')['youtube']['oauth_token'];
        //
        //        if (is_string($token)) {
        //            $token = json_decode($token);
        //        }
        $token = $this->cacher->get(self::CACHE_KEY);
        //        if (!$this->cacher->get(self::CACHE_KEY)) {
        //            $this->cacher->set(self::CACHE_KEY, $token);
        //        }

        // if we have an access token and it's expired, fetch a new token
        $expiryTime = $token['created'] + $token['expires_in'];
        if ($expiryTime >= time()) {
            $this->cacher->set(self::CACHE_KEY, $client->refreshToken($token['refresh_token']));
        }

        return $client;
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
}
