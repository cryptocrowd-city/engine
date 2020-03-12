<?php
namespace Minds\Core\Search\MetricsSync\Resolvers;

use Minds\Core\Search\MetricsSync;

abstract class AbstractMetricResolver implements MetricResolverInterface
{
    /** @var string */
    protected $type;

    /** @var string */
    protected $subtype;

    /** @var int */
    protected $from;

    /** @var int */
    protected $to;

    /** @var Aggegates\Aggregate */
    protected $aggregator;

    /** @var string */
    protected $metricId;

    /**
     * Set the type
     * @param string $type
     * @return self
     */
    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }
    
    /**
     * Set min timestamp
     * @param int $from
     * @return self
     */
    public function setFrom(int $from): self
    {
        $this->from = $from;
        return $this;
    }

    /**
     * Set max timestamp
     * @param int $to
     * @return self
     */
    public function setTo(int $to): self
    {
        $this->to = $to;
        return $this;
    }

    /**
     * Return metrics
     * @return MetricsSync[]
     */
    public function get(): iterable
    {
        $this->aggregator
            ->setLimit(10000)
            ->setType($this->type)
            ->setSubtype($this->subtype)
            ->setFrom($from)
            ->setTo($to);

        $type = $this->type;

        if ($this->subtype) {
            $type = implode(':', [$this->type, $this->subtype]);
        }

        foreach ($this->aggregator->get() as $guid => $uniqueCountValue) {
            $count = $this->getTotalCount($guid);

            $metric = new MetricsSync();
            $metric
                ->setGuid($guid)
                ->setType($type)
                ->setMetric($this->metricId)
                ->setCount($count)
                ->setSynced(time());

            yield $metricsSync;
        }
    }

    /**
     * Return the total count
     * @param string $guid
     * @return int
     */
    abstract protected function getTotalCount(string $guid): int;
}
