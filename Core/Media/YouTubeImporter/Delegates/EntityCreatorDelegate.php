<?php
/**
 * @author: eiennohi.
 */

namespace Minds\Core\Media\YouTubeImporter\Delegates;

use Minds\Core\Entities\Actions\Save;
use Minds\Core\Notification\PostSubscriptions\Manager as PostSubscriptionsManager;
use Minds\Core\Security\ACL;
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

    public function createActivity(Video $video)
    {
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
            ->setTitle($video->getTitle());

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
