<?php

namespace Spec\Minds\Core\Router;

use Minds\Core\Router\Route;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class RouteSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(Route::class);
    }
}
