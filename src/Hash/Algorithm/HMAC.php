<?php

namespace Curfle\Hash\Algorithm;

use Curfle\Agreements\Hash\HashAlgorithm as HashAlgorithm;

class HMAC implements HashAlgorithm
{

    /**
     * Hashes a string with the HMAC algorithm.
     *
     * @param string $string
     * @param string $secret
     * @param string $algorithm
     * @param bool $binary
     * @return string
     */
    public static function hash(string $string, string $secret, string $algorithm = "SHA256", bool $binary = false): string
    {
        return hash_hmac($algorithm, $string, $secret, $binary);
    }
}