<?php

namespace Curfle\Console;

use Curfle\Essence\Application;

class CommandFactory
{
    /**
     * Creates a Connector based on a config.
     *
     * @param Application $app
     * @param string $signature
     * @param callable $resolver
     * @return Command
     */
    public static function fromCallable(Application $app, string $signature, callable $resolver) : Command
    {
        return new Command($app, $signature, $resolver);
    }
}