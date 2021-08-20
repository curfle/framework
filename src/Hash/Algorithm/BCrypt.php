<?php

namespace Curfle\Hash\Algorithm;

use Curfle\Agreements\Hash\HashAlgorithm as HashAlgorithm;

class BCrypt extends PasswordHashAlgorithm implements HashAlgorithm
{

    /**
     * @inheritDoc
     */
    protected static function getHashAlgorithm(): string
    {
        return PASSWORD_BCRYPT;
    }

    /**
     * @inheritDoc
     */
    protected static function getHashOptions(): array
    {
        return [
            "cost" => config("hashing.bcyrpt.rounds", 10)
        ];
    }
}