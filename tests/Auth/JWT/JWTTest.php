<?php

namespace Curfle\Tests\Auth\JWT;


use Curfle\Auth\JWT\JWT;
use Curfle\Support\Env\Env;
use Curfle\Support\Exceptions\Misc\SecretNotPresentException;
use PHPUnit\Framework\TestCase;

class JWTTest extends TestCase
{
    protected function setUp(): void
    {
        Env::set("SECRET", "d5d8984b90b15dbb8e0620170d9fe954d6b56332ed3b9388c0c957894b8edc8d");
    }

    /**
     * Tests the ::generate() function.
     * @throws SecretNotPresentException
     */
    public function testGenerate()
    {
        $token = JWT::generate([
            "iss" => "CURFLE",
            "sub" => 42,
            "aud" => "CURFLE",
            "exp" => 123456789,
            "nbf" => 987654321,
            "iat" => 123456789,
            "jti" => "jwt_unique_id"
        ]);

        $this->assertEquals(
            "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJDVVJGTEUiLCJhdWQiOiJDVVJGTEUiLCJleHAiOjEyMzQ1Njc4OSwibmJmIjo5ODc2NTQzMjEsImlhdCI6MTIzNDU2Nzg5LCJqdGkiOiJqd3RfdW5pcXVlX2lkIiwic3ViIjo0Mn0.9FMRu3YYTArAHqeh-IgecqwSgMAjwchNh_BNOuJDMws",
            $token
        );
    }

    /**
     * Tests the ::verify() function.
     * @throws SecretNotPresentException
     */
    public function testExpiredToken()
    {
        $token = JWT::generate([
            "iss" => "CURFLE",
            "sub" => 42,
            "aud" => "CURFLE",
            "exp" => 123456789,
            "nbf" => 987654321,
            "iat" => 123456789,
            "jti" => ""
        ]);

        $this->assertFalse(
            JWT::valid($token)
        );
    }

    /**
     * Tests the ::verify() function.
     * @throws SecretNotPresentException
     */
    public function testNonValidToken()
    {
        // temporarly change secret
        $tmpSecret = Env::get("SECRET");
        Env::set("SECRET", "THE_OTHER_SECRET");

        $token = JWT::generate([
            "iss" => "CURFLE",
            "sub" => 42,
            "aud" => "CURFLE",
            "exp" => 123456789,
            "nbf" => 987654321,
            "iat" => 123456789,
            "jti" => ""
        ]);

        // reset original secret
        Env::set("SECRET", $tmpSecret);

        $this->assertFalse(
            JWT::valid($token)
        );
    }

    /**
     * Tests the ::verify() function.
     * @throws SecretNotPresentException
     */
    public function testValid()
    {
        $token = JWT::generate([
            "iss" => "CURFLE",
            "sub" => 42,
            "aud" => "CURFLE",
            "exp" => time() + 60,
            "nbf" => 987654321,
            "iat" => 123456789,
            "jti" => ""
        ]);

        $this->assertTrue(
            JWT::valid($token)
        );
    }

    /**
     * Tests the ::verify() function.
     * @throws SecretNotPresentException
     */
    public function testNonValidWithPrefilledValues()
    {
        $token = JWT::generate([
            "sub" => 42,
        ], -1);

        $this->assertFalse(
            JWT::valid($token)
        );
    }

    /**
     * Tests the ::verify() function.
     * @throws SecretNotPresentException
     */
    public function testValidWithPrefilledValues()
    {
        $token = JWT::generate([
            "sub" => 42,
        ]);

        $this->assertTrue(
            JWT::valid($token)
        );
    }
}