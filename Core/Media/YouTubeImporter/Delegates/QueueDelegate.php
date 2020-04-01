<?php
/**
 * @author: eiennohi.
 */

namespace Minds\Core\Media\YouTubeImporter\Delegates;

use Minds\Core\Di\Di;
use Minds\Core\Media\Video\Transcoder\Transcode;
use Minds\Core\Queue\Interfaces\QueueClient;
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
     * @param Video $video
     * @param array $format
     * @return void
     */
    public function onAdd(Video $video, array $format): void
    {
        $this->queueClient
            ->setQueue('YouTubeDownloader')
            ->send([
                'video' => serialize($video),
                'format' => serialize($format),
            ]);
    }
}
