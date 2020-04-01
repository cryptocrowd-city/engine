<?php

namespace Minds\Core\Queue\Runners;

use Minds\Core;
use Minds\Core\Di\Di;
use Minds\Core\Queue\Interfaces;

class YouTubeDownloader implements Interfaces\QueueRunner
{
    public function run()
    {
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        $client = Core\Queue\Client::Build();
        $client->setQueue("YouTubeDownloader")
            ->receive(function ($data) {
                $d = $data->getData();
                $video = unserialize($d['video']);
                $format = unserialize($d['format']);

                echo "[YouTubeDownloader] Received a YouToube download request ({$video->guid})\n";

                /** @var Core\Media\YouTubeImporter\Manager $manager */
                $manager = Di::_()->get('Media\YouTubeImporter\Manager');

                $manager->onQueue($video, $format);
            }, ['max_messages' => 1]);
    }
}
