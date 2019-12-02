<?php

namespace Spec\Minds\Core\Router\Middleware\Kernel;

use Minds\Core\Router\Middleware\Kernel\OauthMiddleware;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class OauthMiddlewareSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(OauthMiddleware::class);
    }
}
