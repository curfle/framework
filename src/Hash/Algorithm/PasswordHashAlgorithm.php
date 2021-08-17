<?php

namespace Curfle\Hash\Algorithm;

use Curfle\Agreements\Hash\HashAlgorithm as HashAlgorithm;

abstract class PasswordHashAlgorithm implements HashAlgorithm
{
    /**
     * Returns the name of the internal php hashing algorithm.
     *
     * @return string
     */
    abstract protected static function getHashAlgorithm() : string;

    /**
     * Returns the options of the internal php hashing algorithm.
     *
     * @return array
     */
    abstract protected static function getHashOptions() : array;

    /**
     * Hashes a string with the BCrypt algorithm.
     *
     * @param string $string
     * @param array|null $options
     * @return string
     */
    public static function hash(string $string, array $options = null): string
    {
        if($options === null)
            $options = static::getHashOptions();

        return password_hash($string, static::getHashAlgorithm(), $options);
    }

    /**
     * Verifies a string against a hash.
     *
     * @param string $string
     * @param string $hash
     * @return bool
     */
    public static function verify(string $string, string $hash): bool
    {
        return password_verify($string, $hash);
    }

    /**
     * Returns if a hash needs to be rehashed.
     *
     * @param string $hash
     * @param array|null $options
     * @return bool
     */
    public static function needsRehash(string $hash, array $options = null): bool
    {
        if($options === null)
            $options = static::getHashOptions();

        return password_needs_rehash($hash, static::getHashAlgorithm(), $options);
    }
}