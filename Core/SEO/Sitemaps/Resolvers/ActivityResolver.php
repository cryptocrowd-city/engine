<?php
/**
 */
namespace Minds\Core\SEO\Sitemaps\Resolvers;

use Minds\Entities\Activity;
use Minds\Core\Entities\Manager as EntitiesManager;
use Minds\Core\SEO\Sitemaps\SitemapUrl;

class ActivityResolver extends AbstractEntitiesResolver
{
    /** @var string */
    protected $type = 'activity';

    /** @var array */
    protected $sort = [ 'votes:up' => 'desc' ];

    public function getUrls(): iterable
    {
        foreach ($this->getRawData() as $raw) {
            $entity = new Activity($raw);
            $lastModified = (new \DateTime)->setTimestamp($entity->time_created);
            $sitemapUrl = new SitemapUrl();
            $sitemapUrl->setLoc($entity->getUrl())
                ->setChangeFreq('never')
                ->setLastModified($lastModified);
            yield $sitemapUrl;
        }
    }
}
