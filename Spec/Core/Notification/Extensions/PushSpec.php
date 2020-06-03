<?php

namespace Spec\Minds\Core\Notification\Extensions;

use Minds\Core\Di\Di;
use Minds\Core\EntitiesBuilder;
use Minds\Core\I18n\Translator;
use Minds\Core\Notification\Extensions\Push;
use Minds\Core\Notification\Notification as NotificationEntity;
use Minds\Entities\Entity;
use Minds\Entities\User;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class PushSpec extends ObjectBehavior
{
    /** @var Config */
    protected $config;

    protected $translator;

    public function let($config, Translator $translator, EntitiesBuilder $builder, User $user)
    {
        $this->config = $config;
        $this->translator = $translator;

        Di::_()->bind('Translator', function () use ($translator) {
            return $translator->getWrappedObject();
        });

        Di::_()->bind('EntitiesBuilder', function () use ($builder) {
            return $builder->getWrappedObject();
        });

        $builder->single(Argument::any())
            ->willReturn($user);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(Push::class);
    }

    public function it_should_construct_commented_on_a_post_message(NotificationEntity $notification, User $user, Entity $entity)
    {
        $notification->getData()
            ->shouldBeCalled()
            ->willReturn(['test' => 123]);
        $user = new User();
        $user->name = 'Bob';
        $user->setLanguage('en');

        $entity = new Entity();
        $entity->owner_guid = '123';

        $this->translator->setLocale(Argument::any())
            ->shouldBeCalled();

        $this->translator->trans(Argument::any(), Argument::any())
            ->shouldBeCalled()
            ->willReturn('Bob commented on a post');

        $this->buildNotificationMessage([
            'notification' => $notification,
            'params' => ['notification_view' => 'comment'],
            'to' => '1',
            'toObj' => $user,
        ], $user, $entity)->shouldBe('Bob commented on a post');
    }

    public function it_should_construct_commented_on_your_post_message(NotificationEntity $notification, User $user, Entity $entity)
    {
        $notification->getData()
            ->shouldBeCalled()
            ->willReturn(['test' => 123]);
        $user = new User();
        $user->name = 'Bob';
        $user->setLanguage('en');

        $entity = new Entity();
        $entity->owner_guid = '123';
        $entity->ownerObj = null;

        $this->translator->setLocale(Argument::any())
            ->shouldBeCalled();

        $this->translator->trans(Argument::any(), Argument::any())
            ->shouldBeCalled()
            ->willReturn('Bob commented on your post');

        $this->buildNotificationMessage([
            'notification' => $notification,
            'params' => ['notification_view' => 'comment'],
            'to' => '123',
            'toObj' => $user,
        ], $user, $entity)->shouldBe('Bob commented on your post');
    }

    public function it_should_construct_commented_on_alices_post_message(NotificationEntity $notification, User $user, Entity $entity)
    {
        $notification->getData()
            ->shouldBeCalled()
            ->willReturn(['test' => 123]);
        $user = new User();
        $user->name = 'Bob';
        $user->setLanguage('en');

        $entity = new Entity();
        $entity->ownerObj = ['name' => 'Alice'];

        $this->translator->setLocale(Argument::any())
            ->shouldBeCalled();

        $this->translator->trans(Argument::any(), Argument::any())
            ->shouldBeCalled()
            ->willReturn('Bob commented on Alice\'s post');

        $this->buildNotificationMessage([
            'notification' => $notification,
            'params' => ['notification_view' => 'comment'],
            'to' => '123',
            'toObj' => $user,
        ], $user, $entity)->shouldBe('Bob commented on Alice\'s post');
    }


    public function it_should_construct_your_liked_your_comment_message(NotificationEntity $notification, User $user, Entity $entity)
    {
        $notification->getData()
            ->shouldBeCalled()
            ->willReturn(['test' => 123]);
        $user = new User();
        $user->name = 'Bob';
        $user->setLanguage('en');

        $entity = new Entity();
        $entity->owner_guid = '123';
        $entity->type = 'comment';

        $this->translator->setLocale(Argument::any())
            ->shouldBeCalled();

        $this->translator->trans(Argument::any(), Argument::any())
            ->shouldBeCalled()
            ->willReturn('Bob voted up your comment');

        $this->buildNotificationMessage([
            'notification' => $notification,
            'params' => ['notification_view' => 'like'],
            'to' => '123',
            'toObj' => $user,
        ], $user, $entity)->shouldBe('Bob voted up your comment');
    }

    public function it_should_construct_your_liked_your_activity_message(NotificationEntity $notification, User $user, Entity $entity)
    {
        $notification->getData()
            ->shouldBeCalled()
            ->willReturn(['test' => 123]);
        $user = new User();
        $user->name = 'Bob';
        $user->setLanguage('en');

        $entity = new Entity();
        $entity->owner_guid = '123';
        $entity->type = 'activity';

        $this->translator->setLocale(Argument::any())
            ->shouldBeCalled();

        $this->translator->trans(Argument::any(), Argument::any())
            ->shouldBeCalled()
            ->willReturn('Bob voted up your activity');

        $this->buildNotificationMessage([
            'notification' => $notification,
            'params' => ['notification_view' => 'like'],
            'to' => '123',
            'toObj' => $user,
        ], $user, $entity)->shouldBe('Bob voted up your activity');
    }

    public function it_should_construct_your_liked_your_object_message(NotificationEntity $notification, User $user, Entity $entity)
    {
        $notification->getData()
            ->shouldBeCalled()
            ->willReturn(['test' => 123]);
        $user = new User();
        $user->name = 'Bob';
        $user->setLanguage('en');

        $entity = new Entity();
        $entity->owner_guid = '123';
        $entity->type = 'object';
        $entity->subtype = 'message';

        $this->translator->setLocale(Argument::any())
            ->shouldBeCalled();

        $this->translator->trans(Argument::any(), Argument::any())
            ->shouldBeCalled()
            ->willReturn('Bob voted up your message');

        $this->buildNotificationMessage([
            'notification' => $notification,
            'params' => ['notification_view' => 'like'],
            'to' => '123',
            'toObj' => $user,
        ], $user, $entity)->shouldBe('Bob voted up your message');
    }

    public function it_should_construct_your_tagged_message(NotificationEntity $notification, User $user, Entity $entity)
    {
        $notification->getData()
            ->shouldBeCalled()
            ->willReturn(['test' => 123]);
        $user = new User();
        $user->name = 'Bob';
        $user->setLanguage('en');

        $entity = new Entity();
        $entity->owner_guid = '123';

        $this->translator->setLocale(Argument::any())
            ->shouldBeCalled();

        $this->translator->trans(Argument::any(), Argument::any())
            ->shouldBeCalled()
            ->willReturn('Bob mentioned you in a post');

        $this->buildNotificationMessage([
            'notification' => $notification,
            'params' => ['notification_view' => 'tag'],
            'to' => '123',
            'toObj' => $user,
        ], $user, $entity)->shouldBe('Bob mentioned you in a post');
    }

    public function it_should_construct_your_reminded_message(NotificationEntity $notification, User $user, Entity $entity)
    {
        $notification->getData()
            ->shouldBeCalled()
            ->willReturn(['test' => 123]);
        $user = new User();
        $user->name = 'Bob';
        $user->setLanguage('en');

        $entity = new Entity();
        $entity->owner_guid = '123';

        $this->translator->setLocale(Argument::any())
            ->shouldBeCalled();

        $this->translator->trans(Argument::any(), Argument::any())
            ->shouldBeCalled()
            ->willReturn('Bob reminded your post');

        $this->buildNotificationMessage([
            'notification' => $notification,
            'params' => ['notification_view' => 'remind'],
            'to' => '123',
            'toObj' => $user,
        ], $user, $entity)->shouldBe('Bob reminded your post');
    }

    public function it_should_construct_your_boost_gift_message(NotificationEntity $notification, User $user, Entity $entity)
    {
        $notification->getData()
            ->shouldBeCalled()
            ->willReturn(['impressions' => 99]);
        $user = new User();
        $user->name = 'Bob';
        $user->setLanguage('en');

        $entity = new Entity();
        $entity->owner_guid = '123';

        $this->translator->setLocale(Argument::any())
            ->shouldBeCalled();

        $this->translator->trans(Argument::any(), Argument::any())
            ->shouldBeCalled()
            ->willReturn('Bob gifted you 99 views');

        $this->buildNotificationMessage([
            'notification' => $notification,
            'params' => ['notification_view' => 'boost_gift'],
            'to' => '123',
            'toObj' => $user,
        ], $user, $entity)->shouldBe('Bob gifted you 99 views');
    }

    public function it_should_construct_your_boost_request_message(NotificationEntity $notification, User $user, Entity $entity)
    {
        $notification->getData()
            ->shouldBeCalled()
            ->willReturn(['points' => 99]);
        $user = new User();
        $user->name = 'Bob';
        $user->setLanguage('en');

        $entity = new Entity();
        $entity->owner_guid = '123';

        $this->translator->setLocale(Argument::any())
            ->shouldBeCalled();

        $this->translator->trans(Argument::any(), Argument::any())
            ->shouldBeCalled()
            ->willReturn('Bob has requested a boost of 99 points');

        $this->buildNotificationMessage([
            'notification' => $notification,
            'params' => ['notification_view' => 'boost_request'],
            'to' => '123',
            'toObj' => $user,
        ], $user, $entity)->shouldBe('Bob has requested a boost of 99 points');
    }

    public function it_should_construct_your_boost_accepted_message(NotificationEntity $notification, User $user, Entity $entity)
    {
        $notification->getData()
            ->shouldBeCalled()
            ->willReturn(['impressions' => 99]);
        $user = new User();
        $user->name = 'Bob';
        $user->setLanguage('en');

        $entity = new Entity();
        $entity->owner_guid = '123';

        $this->translator->setLocale(Argument::any())
            ->shouldBeCalled();

        $this->translator->trans(Argument::any(), Argument::any())
            ->shouldBeCalled()
            ->willReturn('99 views for post were accepted');

        $this->buildNotificationMessage([
            'notification' => $notification,
            'params' => ['notification_view' => 'boost_accepted'],
            'to' => '123',
            'toObj' => $user,
        ], $user, $entity)->shouldBe('99 views for post were accepted'); //????
    }

    public function it_should_construct_your_boost_rejected_message(NotificationEntity $notification, User $user, Entity $entity)
    {
        $notification->getData()
            ->shouldBeCalled()
            ->willReturn(['impressions' => 99]);
        $user = new User();
        $user->name = 'Bob';
        $user->setLanguage('en');

        $entity = new Entity();
        $entity->owner_guid = '123';

        $this->translator->setLocale(Argument::any())
            ->shouldBeCalled();

        $this->translator->trans(Argument::any(), Argument::any())
            ->shouldBeCalled()
            ->willReturn('Your boost request for post was rejected');

        $this->buildNotificationMessage([
            'notification' => $notification,
            'params' => ['notification_view' => 'boost_rejected'],
            'to' => '123',
            'toObj' => $user,
        ], $user, $entity)->shouldBe('Your boost request for post was rejected');
    }

    public function it_should_construct_your_boost_revoked_message(NotificationEntity $notification, User $user, Entity $entity)
    {
        $notification->getData()
            ->shouldBeCalled()
            ->willReturn(['impressions' => 99]);
        $user = new User();
        $user->name = 'Bob';
        $user->setLanguage('en');

        $entity = new Entity();
        $entity->owner_guid = '123';

        $this->translator->setLocale(Argument::any())
            ->shouldBeCalled();

        $this->translator->trans(Argument::any(), Argument::any())
            ->shouldBeCalled()
            ->willReturn('You revoked the boost request for post');

        $this->buildNotificationMessage([
            'notification' => $notification,
            'params' => ['notification_view' => 'boost_revoked'],
            'to' => '123',
            'toObj' => $user,
        ], $user, $entity)->shouldBe('You revoked the boost request for post');
    }

    public function it_should_construct_your_boost_completed_message(NotificationEntity $notification, User $user, Entity $entity)
    {
        $notification->getData()
            ->shouldBeCalled()
            ->willReturn(['impressions' => 99]);
        $user = new User();
        $user->name = 'Bob';
        $user->setLanguage('en');
        $entity = new Entity();
        $entity->owner_guid = '123';

        $this->translator->setLocale(Argument::any())
            ->shouldBeCalled();

        $this->translator->trans(Argument::any(), Argument::any())
            ->shouldBeCalled()
            ->willReturn('99/99 impressions were met for post');

        $this->buildNotificationMessage([
            'notification' => $notification,
            'params' => ['notification_view' => 'boost_completed'],
            'to' => '123',
            'toObj' => $user,
        ], $user, $entity)->shouldBe('99/99 impressions were met for post');
    }

    public function it_should_construct_your_group_invite_message(NotificationEntity $notification, User $user, Entity $entity)
    {
        $notification->getData()
            ->shouldBeCalled()
            ->willReturn(['group' => ['name' => 'test_group']]);
        $user = new User();
        $user->name = 'Bob';
        $user->setLanguage('en');

        $entity = new Entity();
        $entity->owner_guid = '123';

        $this->translator->setLocale(Argument::any())
            ->shouldBeCalled();

        $this->translator->trans(Argument::any(), Argument::any())
            ->shouldBeCalled()
            ->willReturn('Bob invited you to test_group');

        $this->buildNotificationMessage([
            'notification' => $notification,
            'params' => ['notification_view' => 'group_invite'],
            'to' => '123',
            'toObj' => $user,
        ], $user, $entity)->shouldBe('Bob invited you to test_group');
    }

    public function it_should_construct_your_messenger_invite_message(NotificationEntity $notification, User $user, Entity $entity)
    {
        $notification->getData()
            ->shouldBeCalled()
            ->willReturn(['group' => ['name' => 'test_group']]);
        $user = new User();
        $user->name = 'Bob';
        $user->setLanguage('en');
        $entity = new Entity();
        $entity->owner_guid = '123';

        $this->translator->setLocale(Argument::any())
            ->shouldBeCalled();

        $this->translator->trans(Argument::any(), Argument::any())
            ->shouldBeCalled()
            ->willReturn('@Bob wants to chat with you!');

        $this->buildNotificationMessage([
            'notification' => $notification,
            'params' => ['notification_view' => 'messenger_invite'],
            'to' => '123',
            'toObj' => $user,
        ], $user, $entity)->shouldBe('@Bob wants to chat with you!');
    }

    public function getMatchers(): array
    {
        $matchers['beAnArrayOf'] = function ($subject, $count, $class) {
            if (!is_array($subject) || ($count !== null && count($subject) !== $count)) {
                throw new FailureException("Subject should be an array of $count elements");
            }

            $validTypes = true;

            foreach ($subject as $element) {
                if (!($element instanceof $class)) {
                    $validTypes = false;
                    break;
                }
            }

            if (!$validTypes) {
                throw new FailureException("Subject should be an array of {$class}");
            }

            return true;
        };

        return $matchers;
    }
}
