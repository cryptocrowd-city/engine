<?php
/**
 * ConfirmationEmail
 *
 * @author edgebal
 */

namespace Minds\Core\Queue\Runners;

use Exception;
use Minds\Core\Queue\Interfaces;
use Minds\Core\Queue;
use Minds\Core\Events\Dispatcher;

class ConfirmationEmail implements Interfaces\QueueRunner
{
    /**
     * @throws Exception
     */
    public function run()
    {
        $client = Queue\Client::build();
        $client
            ->setQueue('ConfirmationEmail')
            ->receive(function ($data) {
                /** @var Queue\Message $data */
                Dispatcher::trigger('confirmation_email', 'all', $data->getData());
            });
    }
}
