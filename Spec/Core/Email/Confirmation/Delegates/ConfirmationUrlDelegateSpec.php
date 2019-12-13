<?php

namespace Spec\Minds\Core\Email\Confirmation\Delegates;

use Minds\Core\Config;
use Minds\Core\Email\Confirmation\Delegates\ConfirmationUrlDelegate;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ConfirmationUrlDelegateSpec extends ObjectBehavior
{
    /** @var Config */
    protected $config;

    public function let(
        Config $config
    ) {
        $this->config = $config;

        $this->beConstructedWith($config);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(ConfirmationUrlDelegate::class);
    }
}
