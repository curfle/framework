<?php

namespace Curfle\Support\Facades;

use Closure;
use Curfle\Agreements\Console\Kernel as ConsoleKernelAgreement;
use Curfle\Console\Command;
use Curfle\Console\Input;
use Curfle\Console\Output;
use Curfle\Essence\Console\Kernel;

/**
 * @method static Command command(Command|string $command, Closure $callback=null)
 * @method static array getAllCommands()
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
    protected static function getFacadeAccessor() : string
    {
        return ConsoleKernelAgreement::class;
    }
}