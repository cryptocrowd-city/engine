<?php

namespace Minds\Core\Security;

use Minds\Core\Di\Di;
use Minds\Core\Events\Dispatcher;
use Minds\Core\Security\TwoFactor;
use Minds\Exceptions;
use Minds\Helpers\Text;

class Events
{
    /** @var SMS $sms */
    protected $sms;

    /** @var Config $config */
    protected $config;

    public function __construct()
    {
        $this->sms = Di::_()->get('SMS');
        $this->config = $config ?: Di::_()->get('Config');
    }

    public function register()
    {
        Dispatcher::register('create', 'elgg/event/object', [$this, 'onCreateHook']);
        Dispatcher::register('create', 'elgg/event/activity', [$this, 'onCreateHook']);
        Dispatcher::register('update', 'elgg/event/object', [$this, 'onCreateHook']);
    }

    public function onCreateHook($hook, $type, $params, $return = null)
    {
        $object = $params;
        $foundSpam = $this->containsProhibitedDomain($object);

        if ($foundSpam) {
            throw new \Exception("Sorry, you included a reference to a domain name linked to spam (${foundSpam})");
            if (PHP_SAPI != 'cli') {
                forward(REFERRER);
            }
            return false;
        }

        if ($type == 'group' && $this->strposa($object->getBriefDescription(), $this->prohibitedDomains())) {
            return false;
        }

        return true;
    }

    /**
     * Returns true if the object bodies contain a prohibited domain.
     *
     * @param $object - excepts fields description, briefdescription, message and title.
     * @return boolean - true if prohibited domain found.
     */
    public function containsProhibitedDomain($object)
    {
        $prohibitedDomains = $this->config->get('prohibited_domains');
        $bodies = [
            $object->description,
            $object->briefdescription,
            $object->message,
            $object->title
        ];
        foreach ($bodies as $text) {
            $found = Text::strposa($text, $prohibitedDomains);
            if ($found) {
                return $found;
            }
        }
        return false;
    }

    /**
     * Twofactor authentication login hook
     */
    public function onLogin($user)
    {
        global $TWOFACTOR_SUCCESS;

        if ($TWOFACTOR_SUCCESS == true) {
            return true;
        }

        if ($user->twofactor && !\elgg_is_logged_in()) {
            //send the user a twofactor auth code

            $twofactor = new TwoFactor();
            $secret = $twofactor->createSecret(); //we have a new secret for each request

            error_log('2fa - sending SMS to ' . $user->guid);

            $this->sms->send($user->telno, $twofactor->getCode($secret));

            // create a lookup of a random key. The user can then use this key along side their twofactor code
            // to login. This temporary code should be removed within 2 minutes.
            $bytes = openssl_random_pseudo_bytes(128);
            $key = hash('sha512', $user->username . $user->salt . $bytes);

            $lookup = new \Minds\Core\Data\lookup('twofactor');
            $lookup->set($key, ['_guid' => $user->guid, 'ts' => time(), 'secret' => $secret]);

            //forward to the twofactor page
            throw new Exceptions\TwoFactorRequired($key);

            return false;
        }
    }
}
