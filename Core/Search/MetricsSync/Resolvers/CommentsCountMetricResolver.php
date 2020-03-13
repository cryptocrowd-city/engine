<?php
namespace Minds\Core\Search\MetricsSync\Resolvers;

use Minds\Core\Di\Di;
use Minds\Core\Trending\Aggregates;

class CommentsCountMetricResolver extends AbstractMetricResolver
{
    /** @var Manager */
    protected $commentsManager;

    /** @var Aggegates\Aggregate */
    protected $aggregator;

    public function __construct($commentsManager = null, $aggregator = null)
    {
        $this->commentsManager = $commentsManager ?? Di::_()->get('Comments\Manager');
        $this->aggregator = $aggregator ?? new Aggregates\Comments;
    }

    /**
    * Set the type
    * @param string $type
    * @return MetricResolverInterface
    */
    public function setType(string $type): MetricResolverInterface
    {
        if ($type === 'user') {
            throw new \Exception('Can not perform comment count sync on a user');
        }
        return parent::setType($type);
    }

    /**
     * Return the total count
     * @param string $guid
     * @return int
     */
    protected function getTotalCount(string $guid): int
    {
        try {
            return $this->commentsManager->count($guid);
        } catch (Exception $e) {
            return 0;
        }
    }
}
