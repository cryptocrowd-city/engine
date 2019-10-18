<?php
namespace Minds\Core\Queue\Runners;

use Minds\Core;
use Minds\Core\Data;
use Minds\Core\Queue\Interfaces;
use Minds\Core\Queue;
use Minds\Core\Notification\Settings;
use Minds\Entities\User;
use Surge;

/**
 * Email queue runner
 */

class Email implements Interfaces\QueueRunner
{
    public function run()
    {
        $mailer = new Core\Email\Mailer();
        $client = Queue\Client::Build();
        $client->setQueue("Email")
               ->receive(function ($data) use ($mailer) {
                   echo "[email]: Received an email \n";

                   $data = $data->getData();

                   $message = unserialize($data['message']);
                   error_log(var_export($message->from));
                   $mailer->send($message);
                   echo $message->from[0]['email'];
                   echo "[priority email]: delivered to {$message->to[0]['name']} ($message->subject) {$message->from[0]['email']}\n";
               });
        $this->run();
    }
}
