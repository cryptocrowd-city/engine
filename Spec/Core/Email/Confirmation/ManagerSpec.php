<?php

namespace Spec\Minds\Core\Email\Confirmation;

use Minds\Common\Jwt;
use Minds\Core\Config;
use Minds\Core\Email\Confirmation\Delegates;
use Minds\Core\Email\Confirmation\Manager;
use Minds\Core\Queue\Interfaces\QueueClient;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ManagerSpec extends ObjectBehavior
{
    /** @var Config */
    protected $config;

    /** @var Jwt */
    protected $jwt;

    /** @var QueueClient */
    protected $queue;

    /** @var Delegates\ConfirmationUrlDelegate */
    protected $confirmationUrlDelegate;

    public function let(
        Config $config,
        Jwt $jwt,
        QueueClient $queue,
        Delegates\ConfirmationUrlDelegate $confirmationUrlDelegate
    ) {
        $this->config = $config;
        $this->jwt = $jwt;
        $this->queue = $queue;
        $this->confirmationUrlDelegate = $confirmationUrlDelegate;

        $this->beConstructedWith($config, $jwt, $queue, $confirmationUrlDelegate);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(Manager::class);
    }
}
