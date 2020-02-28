<?php
/**
 * @author: eiennohi.
 */

namespace Minds\Core\DeferredOps;

use Minds\Interfaces\ModuleInterface;

class Module implements ModuleInterface
{
    /**
     * OnInit.
     */
    public function onInit()
    {
        $provider = new Provider();
        $provider->register();
    }
}
