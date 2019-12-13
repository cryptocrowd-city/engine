<?php
/**
 * Manager
 *
 * @author edgebal
 */

namespace Minds\Core\Email\Confirmation;

use Exception;
use Minds\Common\Jwt;
use Minds\Core\Config;
use Minds\Core\Di\Di;
use Minds\Core\Queue\Client as QueueClientFactory;
use Minds\Core\Queue\Interfaces\QueueClient;
use Minds\Entities\User;

class Manager
{
    /** @var Config */
    protected $config;

    /** @var Jwt */
    protected $jwt;

    /** @var QueueClient */
    protected $queue;

    /** @var Delegates\ConfirmationUrlDelegate */
    protected $confirmationUrlDelegate;

    /** @var User */
    protected $user;

    /**
     * Manager constructor.
     * @param Config $config
     * @param Jwt $jwt
     * @param QueueClient $queue
     * @param Delegates\ConfirmationUrlDelegate $confirmationUrlDelegate
     * @throws Exception
     */
    public function __construct(
        $config = null,
        $jwt = null,
        $queue = null,
        $confirmationUrlDelegate = null
    ) {
        $this->config = $config ?: Di::_()->get('Config');
        $this->jwt = $jwt ?: new Jwt();
        $this->queue = $queue ?: QueueClientFactory::build();
        $this->confirmationUrlDelegate = $confirmationUrlDelegate ?: new Delegates\ConfirmationUrlDelegate();
    }

    /**
     * @param User $user
     * @return Manager
     */
    public function setUser(User $user): Manager
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @throws Exception
     */
    public function sendEmail(): void
    {
        if ($this->user->isEmailConfirmed()) {
            throw new Exception('User email was already confirmed');
        }

        $config = $this->config->get('email');

        $now = time();
        $expires = $now + $config['confirmation_expires'];

        $token = $this->jwt
            ->setKey($config['confirmation_encryption_key'])
            ->encode([
                'user_guid' => (string) $this->user->guid,
                'code' => $this->jwt->randomString(),
            ], $expires, $now);

        $this->user
            ->setEmailConfirmationToken($token)
            ->save();

        $this->queue
            ->setQueue('ConfirmationEmail')
            ->send([
                'user_guid' => (string) $this->user->guid,
            ]);
    }

    /**
     * @param array $params
     * @return string
     */
    public function generateConfirmationUrl(array $params = []): string
    {
        return $this->confirmationUrlDelegate
            ->generate($this->user, $params);
    }

    /**
     * @param string $jwt
     * @return bool
     * @throws Exception
     */
    public function confirm(string $jwt): bool
    {
        if ($this->user->isEmailConfirmed()) {
            throw new Exception('User email was already confirmed');
        }

        $config = $this->config->get('email');

        $data = $this->jwt
            ->setKey($config['confirmation_encryption_key'])
            ->decode($this->user->getEmailConfirmationToken());

        $confirmation = $this->jwt
            ->setKey($config['confirmation_encryption_key'])
            ->decode($jwt); // Should throw if expired

        if (
            $data['user_guid'] !== $confirmation['user_guid'] ||
            $data['code'] !== $confirmation['code']
        ) {
            throw new Exception('Invalid confirmation token data');
        }

        $this->user
            ->setEmailConfirmationToken('')
            ->setEmailConfirmedAt(time())
            ->save();

        $this->queue
            ->setQueue('WelcomeEmail')
            ->send([
                'user_guid' => (string) $this->user->guid,
            ]);

        return true;
    }
}
