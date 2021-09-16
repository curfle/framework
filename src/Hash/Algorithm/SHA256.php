<?php

namespace Curfle\Hash\Algorithm;

use Curfle\Agreements\Hash\HashAlgorithm as HashAlgorithm;

class SHA256 implements HashAlgorithm
{

    /**
     * Hashes a string with the SHA256 algorithm.
     *
     * @param string $string
     * @param bool $binary
     * @return string
     */
    public static function hash(string $string, bool $binary = false): string
    {
        return hash("sha256", $string, $binary);
    }
}