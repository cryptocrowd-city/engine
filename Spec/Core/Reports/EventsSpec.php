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
    protected $banReasons;

    public function let(EventsDispatcher $dispatcher, Config $config)
    {
        Di::_()->bind('EventsDispatcher', function ($di) use ($dispatcher) {
            return $dispatcher->getWrappedObject();
        });
        $this->dispatcher = $dispatcher;
        $this->config = $config;
        $this->banReasons = [
            1 => [
                'label' => 'Illegal ',
                'reasons' => [
                    1 => '(Terrorism)',
                    2 => '(Paedophilia)',
                    3 => '(Extortion)',
                    4 => '(Fraud)',
                    5 => '(Revenge porn)',
                    6 => '(Sex trafficking)'
                ],
            ],
            2 => [
                'label' => 'Should be marked as explicit ',
                'reasons' => [
                    1 => 'for nudity',
                    2 => 'for pornography',
                    3 => 'for profanity',
                    4 => 'for violence and gore',
                    5 => 'for race, religion or gender',
                ]
            ],
            3 => [ 'label' => 'Encourages or incites violence' ],
            4 => [ 'label' => 'Harassment' ],
            5 => [ 'label' => 'Contains personal and confidential info' ],
            6 => [ 'label' => 'Maliciously targets users (@name, links, images or videos)' ],
            7 => [ 'label' => 'Impersonates someone in a misleading or deceptive manner' ],
            8 => [ 'label' => 'Is spam'],
            9 => [ 'label' => '' ],
            10 => [ 'label' => 'Copyright infringement' ],
            11 => [ 'label' => 'Another reason' ],
            12 => [ 'label' => 'Incorrect use of hashtags' ],
            13 => [ 'label' => 'Malware' ],
            14 => [ 'label' => '' ],
            15 => [ 'label' => 'Trademark infringement' ],
            16 => [ 'label' => 'Token manipulation' ],
        ];
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
        Di::_()->get('Config')->set('ban_reasons', $this->banReasons);

        $this->getBanReasons(1)
            ->shouldReturn("Illegal ");
        
        $this->getBanReasons("1.3")
            ->shouldReturn("Illegal (Extortion)");
                
        $this->getBanReasons("2.3")
            ->shouldReturn("Should be marked as explicit for profanity");

        $this->getBanReasons("3")
            ->shouldReturn("Encourages or incites violence");

        $this->getBanReasons("because reasons")
            ->shouldReturn("because reasons");
    }
}
