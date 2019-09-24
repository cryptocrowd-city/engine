<?php

namespace Spec\Minds\Core\Reports;

use Minds\Core\Di\Di;
use Minds\Core\Events\EventsDispatcher;
use Minds\Core\Reports\Events;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Minds\Core\Config;

class EventsSpec extends ObjectBehavior
{
    /** @var EventsDispatcher */
    protected $dispatcher;
    protected $config;

    public function let(EventsDispatcher $dispatcher, Config $config)
    {
        Di::_()->bind('EventsDispatcher', function ($di) use ($dispatcher) {
            return $dispatcher->getWrappedObject();
        });
        $this->dispatcher = $dispatcher;
        $this->config = $config;
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(Events::class);
    }

    public function it_should_register_the_user_ban_event()
    {
        $this->dispatcher->register('ban', 'user', Argument::any())
            ->shouldBeCalled();

        $this->register();
    }


    public function it_should_discern_ban_reason_text()
    {
        $reasons = [
            1 => 'is illegal',
            2 => 'Should be marked as explicit',
            3 => 'Encourages or incites violence',
        ];
        Di::_()->get('Config')->set('ban_reasons', $reasons);

        $this->getBanReasons(1)
            ->shouldReturn("is illegal");
    }
}
