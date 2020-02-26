<?php
/**
 */
namespace Minds\Core\SEO\Sitemaps\Resolvers;

use Minds\Core\Di\Di;
use Minds\Core\SEO\Sitemaps\SitemapUrl;

class HelpdeskResolver
{
    public function __construct()
    {
        $this->helpdeskQuestionManager = Di::_()->get('Helpdesk\Question\Manager');
    }

    public function getUrls(): iterable
    {
        $questions = $this->helpdeskQuestionManager->getAll([ 'limit' => 5000 ]);
        $i = 0;
        foreach ($questions as $question) {
            ++$i;
            $sitemapUrl = new SitemapUrl();
            $sitemapUrl->setLoc("/help/question/{$question->getUuid()}");
            error_log("$i: {$sitemapUrl->getLoc()}");
            yield $sitemapUrl;
        }
    }
}
