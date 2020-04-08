<?php
/**
 * @author: eiennohi.
 */

namespace Minds\Core\Media\YouTubeImporter\Delegates;

use Minds\Core\Di\Di;
use Minds\Core\Entities\Actions\Save;
use Minds\Core\Feeds\Activity\Manager;
use Minds\Core\Log\Logger;
use Minds\Core\Notification\PostSubscriptions\Manager as PostSubscriptionsManager;
use Minds\Entities\Activity;
use Minds\Entities\Video;

class EntityCreatorDelegate
{
    /** @var Save */
    protected $save;

    /** @var Manager */
    protected $activityManager;

    /** @var PostSubscriptionsManager */
    protected $postsSubscriptionsManager;

    /** @var Logger */
    protected $logger;

    public function __construct($save = null, $activityManager = null, $postsSubscriptionManager = null, $logger = null)
    {
        $this->save = $save ?: new Save();
        $this->activityManager = $activityManager ?: new Manager();
        $this->postsSubscriptionsManager = $postsSubscriptionManager ?: new PostSubscriptionsManager();
        $this->logger = $logger ?: Di::_()->get('Logger');
    }

    public function createActivity(Video $video)
    {
        $activity = $this->activityManager->createFromEntity($video);
        $guid = $this->save->setEntity($activity)->save();

        if ($guid) {
            $this->logger->log("[YouTubeImporter] Created activity ({$guid}) \n");

            // Follow activity
            $this->postsSubscriptionsManager
                ->setEntityGuid($activity->guid)
                ->setUserGuid($activity->getOwnerGUID())
                ->follow();

            // Follow video
            $this->postsSubscriptionsManager
                ->setEntityGuid($video->guid)
                ->setUserGuid($video->getOwnerGUID())
                ->follow();
        } else {
            $this->logger->error("[YouTubeImporter] Failed to create activity ({$guid}) \n");
        }
    }
}
