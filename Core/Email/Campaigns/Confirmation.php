<?php
/**
 * Confirmation
 *
 * @author edgebal
 */

namespace Minds\Core\Email\Campaigns;

use Minds\Core\Di\Di;
use Minds\Core\Email\Confirmation\Manager as ConfirmationManager;
use Minds\Core\Email\Mailer;
use Minds\Core\Email\Message;
use Minds\Core\Email\Template;

class Confirmation extends EmailCampaign
{
    /** @var Template */
    protected $template;

    /** @var Mailer */
    protected $mailer;

    /** @var ConfirmationManager */
    protected $confirmationManager;

    /**
     * Confirmation constructor.
     * @param Template $template
     * @param Mailer $mailer
     * @param ConfirmationManager $confirmationManager
     */
    public function __construct(
        $template = null,
        $mailer = null,
        $confirmationManager = null
    ) {
        $this->template = $template ?: new Template();
        $this->mailer = $mailer ?: new Mailer();
        $this->confirmationManager = $confirmationManager ?: Di::_()->get('Email\Confirmation');
    }

    /**
     * @return Message
     */
    public function build()
    {
        $campaign = 'global';
        $topic = 'confirmation';

        $tracking = [
            '__e_ct_guid' => $this->user->getGUID(),
            'campaign' => $campaign,
            'topic' => $topic,
            'state' => 'new',
        ];

        $this->confirmationManager
            ->setUser($this->user);

        $subject = 'Confirm your Minds email (Action required)';

        $this->template->setTemplate('default.tpl');
        $this->template->setBody('./Templates/confirmation.tpl');
        $this->template->set('user', $this->user);
        $this->template->set('confirmation_url', $this->confirmationManager->generateConfirmationUrl());

        $message = new Message();
        $message
            ->setTo($this->user)
            ->setMessageId(implode(
                '-',
                [ $this->user->guid, sha1($this->user->getEmail()), sha1($campaign . $topic . time()) ]
            ))
            ->setSubject($subject)
            ->setHtml($this->template);

        return $message;
    }

    /**
     * @return void
     */
    public function send()
    {
        if ($this->canSend()) {
            $this->mailer->queue(
                $this->build()
            );

            $this->saveCampaignLog();
        }
    }
}
