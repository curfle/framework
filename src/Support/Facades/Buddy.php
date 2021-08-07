<?php

namespace Curfle\Support\Facades;

use Curfle\Agreements\Console\Kernel as ConsoleKernelAgreement;
use Curfle\Console\Command;
use Curfle\Console\Input;
use Curfle\Console\Output;
use Curfle\Essence\Console\Kernel;

/**
 * @method static Command command(string $command, \Closure $callback)
 * @method static Output run(Input $input)
 *
 * @see Kernel
 */
class Buddy extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getSingletonId() : string
    {
        return ConsoleKernelAgreement::class;
    }
}