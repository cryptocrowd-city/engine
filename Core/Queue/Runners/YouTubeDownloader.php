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
                $user = unserialize($d['user']);
                $video = unserialize($d['video']);

                echo "[YouTubeDownloader] Received a YouTube download request from {$user->username} ({$user->guid})\n";

                /** @var Core\Media\YouTubeImporter\Manager $manager */
                $manager = Di::_()->get('Media\YouTubeImporter\Manager');

                $manager->onQueue($user, $video);
            }, ['max_messages' => 1]);
    }
}
