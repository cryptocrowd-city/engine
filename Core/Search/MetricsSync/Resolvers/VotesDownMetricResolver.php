<?php
namespace Minds\Core\Search\MetricsSync\Resolvers;

class VotesDownMetricResolver extends AbstractVotesMetricResolver
{
    /** @var Aggegates\Aggregate */
    protected $aggregator;

    /** @var string */
    protected $counterMetricId = 'thumbs:down';

    /** @var string */
    protected $metricId = 'votes:down';

    public function __constructor($counters = null, $aggregator = null)
    {
        parent::__constructor($counters);
        $this->aggregator = $aggregator ?? new Aggregates\Votes();
    }
}
