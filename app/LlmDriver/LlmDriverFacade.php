<?php

namespace App\LlmDriver;

use Illuminate\Support\Facades\Facade;

class LlmDriverFacade extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'llm_driver';
    }
}