<?php
/**
 * @author: eiennohi.
 */

namespace Minds\Core\DeferredOps;

use Minds\Common\Repository\Response;
use Minds\Core\Di\Di;

class Manager
{
    /** @var Repository */
    protected $repository;

    public function __construct($repository = null)
    {
        $this->repository = $repository ?: Di::_()->get('DeferredOps\Repository');
    }

    /**
     * @param array $opts
     * @return Response
     * @throws \Exception
     */
    public function getList(array $opts = [])
    {
        return $this->repository->getList($opts);
    }

    /**
     * @param DeferredOp $op
     * @return bool
     * @throws \Exception
     */
    public function add(DeferredOp $op)
    {
        return $this->repository->add($op);
    }

    /**
     * @param DeferredOp $op
     * @return bool
     * @throws \Exception
     */
    public function delete(DeferredOp $op)
    {
        return $this->repository->delete($op);
    }
}
