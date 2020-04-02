<?php
/**
 * @author: eiennohi.
 */

namespace Minds\Core\Media\YouTubeImporter\Delegates;

use Minds\Core\Entities\Actions\Save;
use Minds\Core\Notification\PostSubscriptions\Manager as PostSubscriptionsManager;
use Minds\Entities\Activity;
use Minds\Entities\User;
use Minds\Entities\Video;

class EntityCreatorDelegate
{
    /** @var Save */
    private $save;

    /** @var PostSubscriptionsManager */
    private $manager;

    public function __construct($save = null, $postsSubscriptionManager = null)
    {
        $this->save = $save ?: new Save();
        $this->manager = $postsSubscriptionManager ?: new PostSubscriptionsManager();
    }

    /**
     * Creates video and activity
     * @param User $user
     * @param array $videoDetails
     * @param array $media
     * @return void
     */
    public function onCreate(User $user, array $videoDetails, array $media): void
    {
        // create video entity
        $video = new Video();

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
            $this->manager
                ->setEntityGuid($activity->guid)
                ->setUserGuid($activity->getOwnerGUID())
                ->follow();

            // Follow video
            $this->manager
                ->setEntityGuid($video->guid)
                ->setUserGuid($video->getOwnerGUID())
                ->follow();
        } else {
            echo "[YouTubeDownloader] Failed to create activity ({$guid}) \n";
        }
    }
}
