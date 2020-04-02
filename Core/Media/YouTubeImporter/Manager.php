<?php
/**
 * @author: eiennohi.
 */

namespace Minds\Core\Media\YouTubeImporter;

use Minds\Core\Config\Config;
use Minds\Core\Data\cache\abstractCacher;
use Minds\Core\Di\Di;
use Minds\Core\Entities\Actions\Save;
use Minds\Core\Media\AssetsFactory;
use Minds\Core\Media\YouTubeImporter\Delegates\QueueDelegate;
use Minds\Core\Notification\PostSubscriptions\Manager as PostSubscriptionsManager;
use Minds\Core\Session;
use Minds\Entities\Activity;
use Minds\Entities\User;

class Manager
{
    private const CACHE_KEY = 'youtube:token';

    // preferred qualities, in order of preference
    private const PREFERRED_QUALITIES = ['1080p', '720p', '360p', '240p', '144p'];

    /** @var \Google_Client */
    protected $client;

    /** @var Config */
    protected $config;

    /** @var abstractCacher */
    protected $cacher;

    /** @var QueueDelegate */
    protected $queueDelegate;

    /** @var Save */
    protected $save;

    public function __construct($client = null, $queueDelegate = null, $save = null, $cacher = null, $config = null)
    {
        $this->config = $config ?: Di::_()->get('Config');
        $this->cacher = $cacher ?: Di::_()->get('Cache');
        $this->queueDelegate = $queueDelegate ?? new QueueDelegate();
        $this->save = $save ?: new Save();
        $this->client = $client ?: $this->buildClient();
    }

    /**
     * Connects to a channel
     */
    public function connect()
    {
        return $this->client->createAuthUrl();
    }

    /**
     * Receives the access token and save to yt_connected
     * @param User $user
     * @param string $code
     */
    public function receiveToken(User $user, string $code)
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

        $user->setYouTubeChannels($channels)
            ->save();
    }

    /**
     * Get Videos per channel IF $channelId is registered in yt_channels
     * (uses Repository for getting the status of videos)
     * @param User $user
     * @param string $channelId
     * @param string $status
     * @return array
     * @throws \Exception
     */
    public function getVideos(User $user, string $channelId, string $status)
    {
        $channel = array_filter($user->getYouTubeChannels(), function ($value) use ($channelId) {
            return $value['id'] === $channelId;
        });

        if (!$channel) {
            // TODO refactor to a custom exception
            throw new \Exception('YouTube Channel is not registered to user');
        }

        // Use Minds' access token
        $this->client->setAccessToken($this->cacher->get(self::CACHE_KEY));

        $youtube = new \Google_Service_YouTube($this->client);

        // TODO query the database and get all imported/importing videos

        // get channel
        $channelsResponse = $youtube->channels->listChannels('contentDetails', [
            'id' => $channelId,
        ]);

        $videos = [];

        foreach ($channelsResponse['items'] as $channel) {
            $uploadsListId = $channel['contentDetails']['relatedPlaylists']['uploads'];

            // get videos
            $playlistResponse = $youtube->playlistItems->listPlaylistItems('snippet', [
                'playlistId' => $uploadsListId,
                'maxResults' => 50,
            ]);

            // TODO only add the video if it matches the filter (check with the ones from Repository)
            // TODO: if it matches the filter, include the status in the entity
            foreach ($playlistResponse['items'] as $item) {
                $video = (new Video())
                    ->setYoutubeId($item['snippet']['resourceId']['videoId'])
                    ->setChannelId($item['snippet']['channelId'])
                    ->setChannelTitle($item['snippet']['channelTitle'])
                    ->setDescription($item['snippet']['description'])
                    ->setPublishedAt($item['snippet']['publishedAt'])
                    ->setTitle($item['snippet']['title'])
                    ->setThumbnails($item['snippet']['thumbnails']);

                $videos[] = $video;
            }
        }

        return $videos;
    }

    /**
     * Initiates video import (uses Repository - queues for transcoding)
     * @param User $user
     * @param $channelId
     * @param $videoId
     * @param bool $immediate
     * @throws \IOException
     * @throws \InvalidParameterException
     * @throws \Minds\Exceptions\StopEventException
     */
    public function import(User $user, $channelId, $videoId, $immediate = false)
    {
        // get and decode the data
        parse_str(file_get_contents("https://youtube.com/get_video_info?video_id=" . $videoId), $info);

        $videoData = json_decode($info['player_response'], true);

        // get video details
        $videoDetails = $videoData['videoDetails'];

        // get streaming formats
        $streamingDataFormats = $videoData['streamingData']['formats'];

        $details = [
            'title' => isset($videoDetails['title']) ? $videoDetails['title'] : '',
            'description' => isset($videoDetails['description']) ? $videoDetails['description'] : '',
            'youtube_id' => $videoId,
            'youtube_channel_id' => $channelId,
        ];

        // send to queue so it gets downloaded

        if ($immediate) {
            $this->onQueue($user, $details, $streamingDataFormats);
        } else {
            $this->queueDelegate->onAdd($user, $details, $streamingDataFormats);
        }
    }

    /**
     * @param User $user
     * @param array $videoDetails
     * @param array $formats
     * @throws \IOException
     * @throws \InvalidParameterException
     * @throws \Minds\Exceptions\StopEventException
     */
    public function onQueue(User $user, array $videoDetails, array $formats)
    {
        // find best suitable format
        $format = [];
        $i = 0;

        $length = count(static::PREFERRED_QUALITIES);
        while (count($format) === 0 && $i < $length) {
            foreach ($formats as $f) {
                if ($f['qualityLabel'] === static::PREFERRED_QUALITIES[$i]) {
                    $format = $f;
                }
            }

            $i++;
        }

        echo "[YouTubeDownloader] Downloading YouTube video ({$videoDetails['youtube_id']}) \n";

        // TODO use length from $videoData so we don't have to download the video
        // we need to download the file so we can validate it
        $file = tmpfile();
        $path = stream_get_meta_data($file)['uri'];
        file_put_contents($path, fopen($format['url'], 'r'));

        echo "[YouTubeDownloader] File saved (path: {$path}) \n";

        $media = [
            'file' => $path,
        ];


        // TODO: move entity creation logic to a delegate
        // create video entity
        $video = new \Minds\Entities\Video();

        $video->patch([
            'title' => isset($videoDetails['title']) ? $videoDetails['title'] : '',
            'description' => isset($videoDetails['description']) ? $videoDetails['description'] : '',
            'batch_guid' => 0,
            'access_id' => 2,
            'owner_guid' => $user->guid,
            'full_hd' => $user->isPro(),
            'youtube_id' => $videoDetails['youtube_id'],
            'youtube_channel_id' => $videoDetails['youtube_channel_id'],
        ]);

        $assets = new \Minds\Core\Media\Assets\Video();
        $assets->setEntity($video);

        $assets->validate($media);

        $video->setAssets($assets->upload($media, []));

        echo "[YouTubeDownloader] Saving video ({$video->guid}) \n";

        $video->setACLOverride(true);
        $success = $this->save
            ->setEntity($video)
            ->save(true);

        if (!$success) {
            throw new \Exception('Error saving video');
        }

        // create activity
        $activity = new Activity();
        $activity->setTimeCreated(time());
        $activity->setTimeSent(time());
        $activity->access_id = 2;
        $activity->setMessage($video->getTitle());
        $activity->setFromEntity($video)
            ->setCustom('video', [
                'thumbnail_src' => $video->getIconUrl(),
                'guid' => $video->guid,
                'mature' => false,
            ])
            ->setTitle($video->getTitle())
            ->setACLOverride(true);

        $guid = $this->save->setEntity($activity)->save();

        if ($guid) {
            echo "[YouTubeDownloader] Created activity ({$guid}) \n";

            // Follow activity
            (new PostSubscriptionsManager())
                ->setEntityGuid($activity->guid)
                ->setUserGuid($activity->getOwnerGUID())
                ->follow();

            // Follow video
            (new PostSubscriptionsManager())
                ->setEntityGuid($video->guid)
                ->setUserGuid($video->getOwnerGUID())
                ->follow();
        } else {
            echo "[YouTubeDownloader] Failed to create activity ({$guid}) \n";
        }
    }

    /**
     * Updates a video's status in ES (called by runners)
     * @param $videoId
     */
    public function updateVideoStatus($videoId)
    {
    }

    /**
     * Creates new instance of Google_Client and adds client_id and secret
     */
    private function buildClient()
    {
        $client = new \Google_Client();
        // set auth config
        $client->setClientId($this->config->get('google')['youtube']['client_id']);
        $client->setClientSecret($this->config->get('google')['youtube']['client_secret']);

        // add scopes
        $client->addScope(\Google_Service_YouTube::YOUTUBE_READONLY);

        //add redirect URI
        $client->setRedirectUri($this->config->get('site_url')
            . 'api/v3/media/youtube-importer/oauth/redirect');

        $client->setAccessType('offline');

        // cache this
        $token = $this->config->get('google')['youtube']['oauth_token'];
        if (!$this->cacher->get(self::CACHE_KEY)) {
            $this->cacher->set(self::CACHE_KEY, $token);
        }

        // if we have an access token and it's expired, fetch the refresh token
        $expiryTime = $token['created'] + $token['expires_in'];
        if ($expiryTime >= time()) {
            $this->cacher->set(self::CACHE_KEY, $client->refreshToken($token['refresh_token']));
        }

        return $client;
    }
}
