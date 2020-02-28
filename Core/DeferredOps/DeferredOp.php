<?php
/**
 * @author: eiennohi.
 */

namespace Minds\Core\DeferredOps;

use Minds\Common\Urn;
use Minds\Traits\MagicAttributes;

/**
 * Class DeferredOp
 * @package Minds\Core\DeferredOps
 * @method string getJob()
 * @method DeferredOp setJob(string $value)
 * @method int getNextAttempt()
 * @method DeferredOp setNextAttempt(int $value)
 * @method int getRetries()
 * @method DeferredOp setRetries(int $value)
 * @method int getMaxRetries()
 * @method DeferredOp setMaxRetries(int $value)
 * @method Urn getEntityUrn()
 * @method DeferredOp setEntityUrn(Urn $value)
 */
class DeferredOp
{
    use MagicAttributes;

    protected $job;

    protected $next_attempt;

    protected $entity_urn;

    protected $retries;

    protected $max_retries = 1;}
