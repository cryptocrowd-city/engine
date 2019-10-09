<?php
/**
 */

 namespace Minds\Core\Reports;

use Minds\Core;
use Minds\Entities;
use Minds\Helpers;
use Minds\Core\Di\Di;
use Minds\Core\Analytics\Metrics\Event;
use Minds\Core\Events\Dispatcher;
use Minds\Core\Channels\Delegates\Ban;

class Events
{
    public function __construct()
    {
        $this->config = Di::_()->get('Config');
    }

    public function register()
    {
        Core\Di\Di::_()->get('EventsDispatcher')->register('ban', 'user', function ($event) {
            $user = $event->getParameters();
            //send ban email
            $template = new Core\Email\Template();
            $template
                ->setTemplate()
                ->setBody('banned.tpl')
                ->set('username', $user->username)
                ->set('email', $user->getEmail())
                ->set('reason', $this->getBanReasons($user->ban_reason))
                ->set('user', $user);
            $message = new Core\Email\Message();
            $message->setTo($user)
                ->setMessageId(implode('-', [$user->guid, sha1($user->getEmail()), sha1('register-' . time())]))
                ->setSubject("You are banned from Minds.")
                ->setHtml($template);
            Core\Di\Di::_()->get('Mailer')->queue($message);

            // Record metric

            $event = new Event();
            $event->setType('action')
                ->setProduct('platform')
                ->setUserGuid((string) Core\Session::getLoggedInUser()->guid)
                ->setEntityGuid((string) $user->getGuid())
                ->setAction("ban")
                ->setBanReason($user->ban_reason)
                ->push();
        });
    }

    /**
     * Returns a readable format for a given ban reason, converting
     * tree indicies to their text counterparts, discarding sub-reason.
     *
     * e.g. with the default config, an index of 1 returns "is illegal"
     * an index of 1.1 also returns "is illegal"
     *
     * @param string $index - the given ban reason index
     * @return string the first reason in the ban reason tree, or
     *  if text is in the reason field, it will return that.
     */
    public function getBanReasons($reason)
    {
        $reason = preg_split("/\./", $reason)[0];
        if (is_numeric($reason)) {
            return $this->config->get('ban_reasons')[$reason];
        }
        return $reason;
    }
}
