<?php

namespace Minds\Core\Security;

use Minds\Core\Di\Di;
use Minds\Core\Events\Dispatcher;
use Minds\Core\Security\TwoFactor;
use Minds\Exceptions;
use Minds\Helpers\Text;
use Minds\Core\Config;

class Spam
{
    /** @var Config $config */
    protected $config;

    public function __construct(
        $config = null
    ) {
        $this->config = $config ?: Di::_()->get('Config');
    }
    
    public function check($entity)
    {
        $prohibitedDomains = $this->config->get('prohibited_domains');
        $foundSpam = false;

        switch ($entity->getType()) {
            case 'comment':
                $foundSpam = Text::strposa($entity->getBody(), $prohibitedDomains);
                break;
            case 'activity':
            case 'object':
                if ($entity->getSubtype() === 'blog') {
                    $foundSpam = Text::strposa($entity->getBody(), $prohibitedDomains);
                    break;
                }
                $foundSpam = Text::strposa($entity->getDescription(), $prohibitedDomains);
                break;
            case 'user':
                $foundSpam = Text::strposa($entity->briefdescription, $prohibitedDomains);
                break;
            case 'group':
                $foundSpam = Text::strposa($entity->getBriefDescription(), $prohibitedDomains);
                break;
            default:
                error_log("[spam-check]: $entity->type:$entity->subtype not supported");
         }

        if ($foundSpam) {
            throw new \Exception("Sorry, you included a reference to a domain name linked to spam (${foundSpam})");
            return true;
        }
        return $foundSpam ? true : false;
    }
}
