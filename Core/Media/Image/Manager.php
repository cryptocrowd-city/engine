<?php
/**
 * Image Manager
 */
namespace Minds\Core\Media\Image;

use Minds\Core\Config;
use Minds\Core\Di\Di;
use Minds\Core\Session;
use Minds\Entities\Entity;
use Minds\Entities\Activity;
use Minds\Entities\Image;
use Minds\Entities\Video;
use Minds\Core\Comments\Comment;
use Minds\Core\Security\SignedUri;
use Minds\Core\Wire\Paywall\PaywallEntityInterface;
use Lcobucci\JWT;
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Zend\Diactoros\Uri;

class Manager
{
    /** @var Config $config */
    private $config;

    /** @var SignedUri $signedUri */
    private $signedUri;

    public function __construct($config = null, $signedUri = null)
    {
        $this->config = $config ?? Di::_()->get('Config');
        $this->signedUri = $signedUri ?? new SignedUri;
    }

    /**
     * Return a public asset uri for entity type
     * @param Entity $entity
     * @param string $size
     * @return string
     */
    public function getPublicAssetUri($entity, $size = 'xlarge'): string
    {
        $uri = null;
        $asset_guid = null;
        $lastUpdated = null;
        switch (get_class($entity)) {
            case Activity::class:
                switch ($entity->get('custom_type')) {
                    case "batch":
                        $asset_guid = $entity->get('entity_guid');
                        break;
                    default:
                        $asset_guid = $entity->get('entity_guid');
                }
                $lastUpdated = $entity->get('last_updated');
                break;
            case Image::class:
                $asset_guid = $entity->getGuid();
                break;
            case Video::class:
                $asset_guid = $entity->getGuid();
                $lastUpdated = $entity->get('last_updated');
                break;
            case Comment::class:
                $asset_guid = $entity->getAttachments()['attachment_guid'];
                break;
        }

        $path = 'fs/v1/thumbnail/' . $asset_guid . '/' . $size . '/' . $lastUpdated;
        $uri = $this->config->get('cdn_url') . $path;

        if (
            $entity->access_id !== ACCESS_PUBLIC
            || $entity->owner_guid != $entity->container_guid
            || ($entity instanceof PaywallEntityInterface && $entity->isPayWall())
        ) {
            $uri = $this->config->get('site_url') . $path;
            $uri = $this->signUri($uri);

            // TODO: move this over to paywall manager via a hook (or something?)
            $loggedInUser = Session::getLoggedInUser();
            if ($entity instanceof PaywallEntityInterface && $entity->isPayWallUnlocked() || ($loggedInUser && $entity->owner_guid == $loggedInUser->getGuid())) {
                $uri .= "&unlock_paywall=" . time();
            }
        }

        return $uri;
    }

    /**
     * Sign a uri and return the uri with the signature attached
     * @param string $uri
     * @return string
     */
    private function signUri($uri, $pub = ""): string
    {
        $now = new \DateTime();
        $expires = $now->modify('midnight + 30 days')->getTimestamp();
        return $this->signedUri->sign($uri, $expires);
    }

    /**
     * Config signed uri
     * @param string $uri
     * @return string
     */
    public function confirmSignedUri($uri): bool
    {
        return $this->signedUri->confirm($uri);
    }
}
