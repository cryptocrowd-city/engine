<?php
/**
 * @author: eiennohi.
 */

namespace Minds\Core\Media\YouTubeImporter;

use Minds\Core\Config\Config;
use Minds\Core\Data\cache\abstractCacher;
use Minds\Core\Di\Di;
use Minds\Entities\User;

class Manager
{
    private const CACHE_KEY = 'youtube:token';

    /** @var \Google_Client */
    protected $client;

    /** @var Config */
    protected $config;

    /** @var abstractCacher */
    protected $cacher;

    public function __construct($client = null, $cacher = null, $config = null)
    {
        $this->config = $config ?: Di::_()->get('Config');
        $this->cacher = $cacher ?: Di::_()->get('Cache');
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

        $user->setYouTubeChannels($token)
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
                    ->setYoutubeId($item['id'])
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
     */
    public function import(User $user, $channelId, $videoId)
    {
        // TODO query video from youtube and get its data
        // TODO trigger the queue
        // TODO find out how to download video
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
