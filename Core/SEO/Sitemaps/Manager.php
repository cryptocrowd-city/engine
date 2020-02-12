<?php

namespace Minds\Core\SEO\Sitemaps;

use Minds\Core\Config;
use Minds\Core\Di\Di;
use Spatie\Sitemap\SitemapGenerator;

class Manager
{
    /** @var Config */
    protected $config;

    protected $resolvers = [
        Resolvers\ActivityResolver::class,
        Resolvers\UsersResolver::class,
    ];

    public function __construct($dynamicMaps = null)
    {
        $this->config = Di::_()->get('Config');
    }

    /**
     * Build the sitemap
     * @return void
     */
    public function build(): void
    {
        $outputDir = getcwd();
        $generator = new \Icamys\SitemapGenerator\SitemapGenerator('', $outputDir);
        $generator->setSitemapFileName("sitemap.xml");
        $generator->setSitemapIndexFileName("sitemap-index.xml");
        $generator->setMaxURLsPerSitemap(50000);

        foreach ($this->resolvers as $resolver) {
            $resolver = new $resolver;
            foreach ($resolver->getUrls() as $sitemapUrl) {
                $generator->addURL(
                    $sitemapUrl->getLoc(),
                    $sitemapUrl->getLastModified(),
                    $sitemapUrl->getChangeFreq(),
                    $sitemapUrl->getPriority()
                );
            }
        }

        $generator->createSitemap();
        $generator->writeSitemap();
    }
}
