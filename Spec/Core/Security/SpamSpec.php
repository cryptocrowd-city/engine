<?php

namespace Spec\Minds\Core\Security;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Minds\Core\Blogs\Blog;
use Minds\Core\Config;
use Minds\Core\Comments\Comment;
use Minds\Entities\User;
use Minds\Entities\Group;
use Minds\Entities\Entity;

class SpamSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType('Minds\Core\Security\Spam');
    }

    public function it_should_detect_spam_in_a_blog(Blog $blog, Config $config)
    {
        $config->get('prohibited_domains')
            ->shouldBeCalled()
            ->willReturn(['bit.ly']);

        $this->beConstructedWith($config);

        $blog->getBody()->shouldBeCalled()->willReturn('test bit.ly test');
        $blog->getType()->shouldBeCalled()->willReturn('object');
        $blog->getSubtype()->shouldBeCalled()->willReturn('blog');
        
        $this->shouldThrow(new \Exception("Sorry, you included a reference to a domain name linked to spam (bit.ly)"))
            ->duringCheck($blog);
    }

    public function it_should_detect_spam_in_a_comment(Comment $comment, Config $config)
    {
        $config->get('prohibited_domains')
            ->shouldBeCalled()
            ->willReturn(['bit.ly']);

        $this->beConstructedWith($config);

        $comment = new Comment();
        $comment->setBody('test bit.ly test');
        $comment->setType('comment');
        
        $this->shouldThrow(new \Exception("Sorry, you included a reference to a domain name linked to spam (bit.ly)"))
            ->duringCheck($comment);
    }

    public function it_should_detect_spam_in_a_user(User $user, Config $config)
    {
        $config->get('prohibited_domains')
            ->shouldBeCalled()
            ->willReturn(['bit.ly']);

        $this->beConstructedWith($config);

        $user = new User('123');
        $user['briefdescription'] = 'test bit.ly test';
        $user['type'] = 'user';
        
        $this->shouldThrow(new \Exception("Sorry, you included a reference to a domain name linked to spam (bit.ly)"))
            ->duringCheck($user);
    }
   
    public function it_should_detect_spam_in_a_group(Group $group, Config $config)
    {
        $config->get('prohibited_domains')
            ->shouldBeCalled()
            ->willReturn(['bit.ly']);

        $this->beConstructedWith($config);

        $group = new Group();
        $group->setBriefdescription('test bit.ly test');
        $group->setType('group');
        
        $this->shouldThrow(new \Exception("Sorry, you included a reference to a domain name linked to spam (bit.ly)"))
            ->duringCheck($group);
    }







    public function it_should_detect_NO_spam_in_a_blog(Blog $blog, Config $config)
    {
        $config->get('prohibited_domains')
            ->shouldBeCalled()
            ->willReturn(['bit.ly']);

        $this->beConstructedWith($config);

        $blog->getBody()->shouldBeCalled()->willReturn('test bit.nospam test');
        $blog->getType()->shouldBeCalled()->willReturn('object');
        $blog->getSubtype()->shouldBeCalled()->willReturn('blog');
        
        $this->check($blog)->shouldReturn(false);
    }

    public function it_should_detect_NO_spam_in_a_comment(Comment $comment, Config $config)
    {
        $config->get('prohibited_domains')
            ->shouldBeCalled()
            ->willReturn(['bit.ly']);

        $this->beConstructedWith($config);

        $comment = new Comment();
        $comment->setBody('test bit.nospam test');
        $comment->setType('comment');
        
        $this->check($comment)->shouldReturn(false);
    }

    public function it_should_detect_NO_spam_in_a_user(User $user, Config $config)
    {
        $config->get('prohibited_domains')
            ->shouldBeCalled()
            ->willReturn(['bit.ly']);

        $this->beConstructedWith($config);

        $user = new User('123');
        $user['briefdescription'] = 'test bit.nospam test';
        $user['type'] = 'user';
        
        $this->check($user)->shouldReturn(false);
    }
   
    public function it_should_detect_NO_spam_in_a_group(Group $group, Config $config)
    {
        $config->get('prohibited_domains')
            ->shouldBeCalled()
            ->willReturn(['bit.ly']);

        $this->beConstructedWith($config);

        $group = new Group();
        $group->setBriefdescription('test bit.nospam test');
        $group->setType('group');
        
        $this->check($group)->shouldReturn(false);
    }
}
