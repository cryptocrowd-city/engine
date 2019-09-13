<?php

namespace Spec\Minds\Core\Security;

use Minds\Core\Security\SignedUri;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class SignedUriSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(SignedUri::class);
    }

    // public function it_sign_a_uri()
    // {
    //     $this->sign("https://minds-dev/foo")
    //         ->shouldBe("https://minds-dev/fo");
    // }
}
