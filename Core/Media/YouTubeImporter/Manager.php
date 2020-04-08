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
use Minds\Core\Media\YouTubeImporter\Delegates\EntityCreatorDelegate;
use Minds\Core\Media\YouTubeImporter\Delegates\QueueDelegate;
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

    /** @var Logger */
    protected $logger;

    public function __construct($repository = null, $client = null, $queueDelegate = null, $entityDelegate = null, $save = null, $cacher = null, $call = null, $config = null, $logger = null)
    {
        $this->repository = $repository ?: Di::_()->get('Media\YouTubeImporter\Repository');
        $this->config = $config ?: Di::_()->get('Config');
        $this->cacher = $cacher ?: Di::_()->get('Cache');
        $this->queueDelegate = $queueDelegate ?: new QueueDelegate();
        $this->entityDelegate = $entityDelegate ?: new EntityCreatorDelegate();
        $this->save = $save ?: new Save();
        $this->call = Di::_()->get('Database\Cassandra\Indexes') ?: new Call('entities_by_time');
        $this->client = $client ?: $this->buildClient();
        $this->logger = $logger ?: Di::_()->get('Logger');
    }

    /**
     * Connects to a channel
     */
    public function connect(): void
    {
        return $this->client->createAuthUrl();
    }

    /**
     * Receives the access token and save to yt_connected
     * @param User $user
     * @param string $code
     */
    public function fetchToken(User $user, string $code): void
    {
        $token = $this->client->fetchAccessTokenWithAuthCode($code);

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
     * @throws \IOException
     * @throws \InvalidParameterException
     * @throws \Exception
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

        // if status is 'queued' or 'completed', then we don't consult youtube
        if (isset($opts['status']) && in_array($opts['status'], ['queued', 'completed'], true)) {
            return $this->repository->getVideos($opts);
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
                $response = $this->repository->getVideos(['youtube_id' => $youtubeId])->toArray();

                if (count($response) > 0) {
                    $videos[] = $response[0];
                } else {
                    $video = (new Video())
                        ->setYoutubeId($item['snippet']['resourceId']['videoId'])
                        ->setYoutubeChannelId($item['snippet']['channelId'])
                        ->setDescription($item['snippet']['description'])
                        ->setTitle($item['snippet']['title'])
                        ->setYouTubeThumbnail($item['snippet']['thumbnails']->getHigh()['url']);

                    $videos[] = $video;
                }
            }
        }
        return $videos;
    }

    /**
     * Initiates video import (uses Repository - queues for transcoding)
     * @param User $user
     * @param $channelId
     * @param $videoId
     * @throws \IOException
     * @throws \InvalidParameterException
     * @throws \Exception
     */
    public function import(User $user, $channelId, $videoId): void
    {
        // get and decode the data
        parse_str(file_get_contents("https://youtube.com/get_video_info?video_id=" . $videoId), $info);

        $videoData = json_decode($info['player_response'], true);

        // get video details
        $videoDetails = $videoData['videoDetails'];

        // get streaming formats
        $streamingDataFormats = $videoData['streamingData']['formats'];

        // validate length
        (new \Minds\Core\Media\Assets\Video())->validate(['length' => $videoDetails['lengthSeconds'] / 60]);

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
            'owner_guid' => $user->guid,
            'full_hd' => $user->isPro(),
            'youtube_id' => $videoId,
            'youtube_channel_id' => $channelId,
            'transcoding_status' => 'queued',
            'chosen_format_url' => $format['url'],
        ]);

        // check if we're below the threshold
        if ($this->getOwnersEligibility([$user->guid])[$user->guid] < $this->getThreshold()) {
            $this->queue($user, $video);
        }
    }

    /**
     * Sends a video to a queue to be transcoded
     * @param User $user
     * @param Video $video
     */
    public function queue(User $user, Video $video): void
    {
        // send to queue so it gets downloaded
        $this->queueDelegate->onAdd($user, $video);
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
     * @param User $user
     * @param Video $video
     * @throws \Minds\Exceptions\StopEventException
     */
    public function onQueue(User $user, Video $video): void
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

        $assets = new \Minds\Core\Media\Assets\Video();
        $assets->setEntity($video);

        $assets->validate($media);

        $this->logger->info("[YouTubeImporter] Initiating upload to S3 ({$video->guid}) \n");

        $video->setAssets($assets->upload($media, []));

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
     * @param string $channelId
     * @param bool $subscribe
     * @return bool returns true if it succeeds
     */
    public function subscribe(string $channelId, bool $subscribe): bool
    {
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

        return preg_match('200', $http_response_header[0]) === 1;
    }

    /**
     * Imports a newly added YT video. This is called when the hook receives a new update.
     * @param string $videoId
     * @param string $channelId
     * @throws \IOException
     * @throws \InvalidParameterException
     */
    public function receiveNewVideo(string $videoId, string $channelId): void
    {
        // see if we have a video like this already saved
        $response = $this->repository->getVideos(['youtube_id' => $videoId]);

        // if the video isn't there, we'll download it
        if ($response->count() === 0) {
            // fetch User associated with this channelId
            $result = $this->call->getRow("yt_channel:user:{$channelId}");

            if (count($result) === 0) {
                // no User is associated with this youtube channel
                return;
            }

            $user = new User($result[$channelId]);

            if ($user->isBanned() || $user->getDeleted()) {
                return;
            }

            $this->import($user, $videoId, $channelId);
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
            . 'api/v3/media/youtube-importer/oauth/redirect');

        $client->setAccessType('offline');

        // cache this
        $token = $this->config->get('google')['youtube']['oauth_token'];
        if (!$this->cacher->get(self::CACHE_KEY)) {
            $this->cacher->set(self::CACHE_KEY, $token);
        }

        // if we have an access token and it's expired, fetch a new token
        $expiryTime = $token['created'] + $token['expires_in'];
        if ($expiryTime >= time()) {
            $this->cacher->set(self::CACHE_KEY, $client->refreshToken($token['refresh_token']));
        }

        return $client;
    }
}
