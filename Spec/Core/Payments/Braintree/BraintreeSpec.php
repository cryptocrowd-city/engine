<?php

namespace Spec\Minds\Core\Payments\Braintree;

use Braintree\Configuration;
use Minds\Core\Config\Config;
use PhpSpec\ObjectBehavior;

class BraintreeSpec extends ObjectBehavior
{
    public function it_is_initializable(Configuration $btConfig, Config $config)
    {
        $this->beConstructedWith($btConfig, $config);
        $this->shouldHaveType('Minds\Core\Payments\Braintree\Braintree');
    }
}
