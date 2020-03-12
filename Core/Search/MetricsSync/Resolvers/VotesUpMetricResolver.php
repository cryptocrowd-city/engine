<?php
namespace Minds\Core\Search\MetricsSync\Resolvers;

class VotesUpMetricResolver extends AbstractVotesMetricResolver
{
    /** @var Aggegates\Aggregate */
    protected $aggregator;

    /** @var string */
    protected $counterMetricId = 'thumbs:up';

    /** @var string */
    protected $metricId = 'votes:up';

    public function __constructor($counters = null, $aggregator = null)
    {
        parent::__constructor($counters);
        $this->aggregator = $aggregator ?? new Aggregates\Votes();
    }
}
