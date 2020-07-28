<?php

namespace Minds\Exceptions;

class SupportTierNotMetException extends \Exception
{
    public function __construct()
    {
        $this->message = 'You do not meet the subscription tier requirements to interact with this entity.';
    }
}
