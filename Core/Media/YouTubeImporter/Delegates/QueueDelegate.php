<?php
/**
 * @author: eiennohi.
 */

namespace Minds\Core\Media\YouTubeImporter\Delegates;

use Minds\Core\Di\Di;
use Minds\Core\Queue\Interfaces\QueueClient;
use Minds\Entities\User;
use Minds\Entities\Video;

class QueueDelegate
{
    /** @var QueueClient */
    private $queueClient;

    public function __construct($queueClient = null)
    {
        $this->queueClient = $queueClient ?? Di::_()->get('Queue');
    }

    /**
     * Add a download to the queue
     * @param User $user
     * @param Video $video
     * @return void
     */
    public function onAdd(User $user, Video $video): void
    {
        $this->queueClient
            ->setQueue('YouTubeDownloader')
            ->send([
                'user' => serialize($user),
                'video' => serialize($video),
            ]);
    }
}
