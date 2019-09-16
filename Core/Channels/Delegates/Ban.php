<?php
/**
 * BanDelegate.
 *
 * @author emi
 */

namespace Minds\Core\Channels\Delegates;

use Minds\Core\Di\Di;
use Minds\Core\Events\EventsDispatcher;
use Minds\Entities\User;

class Ban
{
    /** @var EventsDispatcher */
    protected $eventsDispatcher;

    private const REASONS = array(
        1 => 'is illegal',
        2 => 'Should be marked as explicit',
        3 => 'Encourages or incites violence',
        4 => 'Harassment',
        5 => 'contains personal and confidential info' ,
        6 => 'Maliciously targets users (@name, links, images or videos)',
        7 => 'Impersonates someone in a misleading or deceptive manner',
        8 => 'is spam',
        10 => 'is a copyright infringement',
        11 => 'Another reason',
        12 => 'Incorrect use of hashtags',
        13 => 'Malware',
        15 => 'Trademark infringement',
        16 => 'Token manipulation',
    );

    public function __construct($eventsDispatcher = null)
    {
        $this->eventsDispatcher = $eventsDispatcher ?: Di::_()->get('EventsDispatcher');
    }

    /**
     * @param User $user
     * @param string $banReason
     * @return bool
     */
    public function ban(User $user, $banReason = '', $refreshCache = true)
    {
        $user->ban_reason = $banReason;
        $user->banned = 'yes';
        $user->code = '';

        $saved = (bool) $user->save();

        if ($saved) {
            if ($refreshCache) {
                \cache_entity($user);
            }

            $this->eventsDispatcher->trigger('ban', 'user', $user);
        }

        return $saved;
    }

    /**
     * Gets the text of a reason associated with a ban_reason index.
     *
     * @param int $i ban_reason index.
     * @return string text reason e.g. "is illegal".
     */
    public function getReasonText($i) 
    {
        return static::REASONS[$i]; 
    }
}
