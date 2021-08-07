<?php

namespace Curfle\Console;


class CommandFactory
{
    /**
     * Creates a Connector based on a config.
     *
     * @param string $signature
     * @param callable $resolver
     * @return Command
     */
    public static function fromCallable(string $signature, callable $resolver) : Command
    {
        return new Command($signature, $resolver);
    }
}