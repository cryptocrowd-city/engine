<?php

namespace Spec\Minds\Core\SEO\Sitemaps\Resolvers;

use Minds\Core\Data\ElasticSearch\Scroll;
use Minds\Core\SEO\Sitemaps\Resolvers\UsersResolver;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class UsersResolverSpec extends ObjectBehavior
{
    protected $scroll;

    public function let(Scroll $scroll)
    {
        $this->beConstructedWith($scroll);
        $this->scroll = $scroll;
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(UsersResolver::class);
    }

    public function it_should_return_iterable_of_users()
    {
        $this->scroll->request(Argument::any())
            ->shouldBeCalled()
            ->willReturn([
                [
                    '_source' => [
                        'username' => 'mark'
                    ]
                ]
            ]);
        $this->getUrls()->shouldHaveCount(1);
    }
}
