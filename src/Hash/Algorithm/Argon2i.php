<?php

namespace Curfle\Hash\Algorithm;

use Curfle\Agreements\Hash\Algorithm as HashAlgorithm;

class Argon2i extends PasswordAlgorithm implements HashAlgorithm
{

    /**
     * @inheritDoc
     */
    protected static function getHashAlgorithm(): string
    {
        return PASSWORD_ARGON2I;
    }

    /**
     * @inheritDoc
     */
    protected static function getHashOptions(): array
    {
        return [
            "memory_cost" => PASSWORD_ARGON2_DEFAULT_MEMORY_COST,
            "time_cost" => PASSWORD_ARGON2_DEFAULT_TIME_COST,
            "threads" => PASSWORD_ARGON2_DEFAULT_THREADS
        ];
    }
}