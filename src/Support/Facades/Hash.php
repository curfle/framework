<?php

namespace Curfle\Support\Facades;

use Curfle\Agreements\Hash\HashAlgorithm;

/**
 * @method static HashAlgorithm algorithm(string $name = null)
 * @method static string hash(string $string, array $options = null)
 * @method static bool verify(string $string, string $hash)
 * @method static bool needsRehash(string $hash, array $options = null)
 *
 * @see \Curfle\Hash\HashManager
 */
class Hash extends Facade
{

    /**
     * @inheritDoc
     */
    protected static function getFacadeAccessor(): string
    {
        return "hash";
    }
}