<?php

namespace Curfle\Auth\JWT;

use Curfle\Hash\Algorithm\HMAC;
use Curfle\Support\Exceptions\Auth\IncorrectJWTFormatException;
use Curfle\Support\Exceptions\Misc\SecretNotPresentException;
use Curfle\Utilities\Base64;
use Curfle\Utilities\JSON;

class JWT
{
    /**
     * Generates a new JWT. Lifes for 30 days by default.
     *
     * @param array $payload
     * @param int $lifetime
     * @param string $algorithm
     * @return string
     * @throws SecretNotPresentException
     */
    public static function generate(array $payload, int $lifetime = 60 * 60 * 24 * 30, string $algorithm = "HS256"): string
    {
        $secret = env("SECRET", null);

        if ($secret === null)
            throw new SecretNotPresentException("The SECRET property is not defined in your .env file");

        // fill header
        $header = [
            "typ" => "JWT",
            "alg" => $algorithm
        ];

        // fill payload
        $payload = array_merge(
            [
                "iss" => env("JWT_ISS", null),      // issuer
                "aud" => env("JWT_AUD", null),      // audience
                "exp" => time() + $lifetime,                    // expiration time
                "nbf" => time(),                                // not before
                "iat" => time(),                                // issued at time
                "jti" => uniqid("jwt_")                   // unique identifier for jwt
            ],
            $payload
        );

        // base-64-url encode header and payload
        $encodedHeader = Base64::urlEncode(JSON::from($header));
        $encodedPayload = Base64::urlEncode(JSON::from($payload));

        // create the signature
        $signature = HMAC::hash(
            "$encodedHeader.$encodedPayload",
            $secret
        );
        $encodedSignature = Base64::urlEncode($signature);

        // create a jwt
        return "$encodedHeader.$encodedPayload.$encodedSignature";
    }

    /**
     * Returns if a token is valid against the environment secret (and timestamp if available and wanted).
     *
     * @param string $token
     * @param bool $checkExpIfAvailable
     * @return bool
     * @throws SecretNotPresentException
     */
    public static function valid(string $token, bool $checkExpIfAvailable = true): bool
    {
        $secret = env("SECRET", null);

        if ($secret === null)
            throw new SecretNotPresentException("The SECRET property is not defined in your .env file");

        // split the token
        [$header, $payload, $signature] = explode(".", $token);

        $payloadParsed = JSON::parse(Base64::urlDecode($payload));

        // check for expiration timestamp if wanted and possible
        if ($checkExpIfAvailable && $payloadParsed !== null && array_key_exists("exp", $payloadParsed))
            if ($payloadParsed["exp"] < time())
                return false;

        // build new header and payload signatures
        $correctSignature = Base64::urlEncode(
            HMAC::hash(
                "$header.$payload",
                $secret
            )
        );

        // validate signature against correct signature
        return $signature === $correctSignature;
    }

    /**
     * Returns the tokens' content.
     *
     * @param string $token
     * @return array|null
     * @throws IncorrectJWTFormatException
     * @throws SecretNotPresentException
     */
    public static function decode(string $token): array|null
    {
        $secret = env("SECRET", null);

        if ($secret === null)
            throw new SecretNotPresentException("The SECRET property is not defined in your .env file.");

        // split the token
        $parts = explode(".", $token);
        if (count($parts) !== 3)
            throw new IncorrectJWTFormatException("The JWT provided is not formatted correctly.");

        [$header, $payload, $signature] = $parts;

        return JSON::parse(Base64::urlDecode($payload));
    }

    /**
     * Returns the tokens' content.
     *
     * @param string $token
     * @return array|null
     * @throws IncorrectJWTFormatException
     * @throws SecretNotPresentException
     */
    public static function decodeHeader(string $token): array|null
    {
        $secret = env("SECRET", null);

        if ($secret === null)
            throw new SecretNotPresentException("The SECRET property is not defined in your .env file");

        // split the token
        $parts = explode(".", $token);
        if (count($parts) !== 3)
            throw new IncorrectJWTFormatException("The JWT provided is not formatted correctly.");

        [$header, $payload, $signature] = $parts;

        return JSON::parse(Base64::urlDecode($header));
    }
}