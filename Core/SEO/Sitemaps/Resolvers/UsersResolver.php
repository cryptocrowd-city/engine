<?php
/**
 */
namespace Minds\Core\SEO\Sitemaps\Resolvers;

use Minds\Entities\User;
use Minds\Core\SEO\Sitemaps\SitemapUrl;

class UsersResolver extends AbstractEntitiesResolver
{
    protected $type = 'user';

    public function getUrls(): iterable
    {
        foreach ($this->getRawData() as $raw) {
            $entity = new User($raw);
            $lastModified = (new \DateTime)->setTimestamp($entity->last_login ?: $entity->time_created);
            $sitemapUrl = new SitemapUrl();
            $sitemapUrl->setLoc('https://www.minds.com/'.$entity->username)
                ->setChangeFreq('daily')
                ->setLastModified($lastModified);
            yield $sitemapUrl;
        }
    }
}
