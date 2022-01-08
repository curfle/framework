<?php

namespace Curfle\Tests\Utilities;

use Curfle\Utilities\Faker\Faker;
use PHPUnit\Framework\TestCase;

class FakerTest extends TestCase
{
    /**
     * Tests the ::firstname() function.
     */
    public function testFirstname()
    {
        self::assertMatchesRegularExpression(
            "/([A-Z]|[a-z])+/",
            Faker::firstname()
        );
    }

    /**
     * Tests the ::lastname() function.
     */
    public function testLastname()
    {
        self::assertMatchesRegularExpression(
            "/([A-Z]|[a-z])+/",
            Faker::firstname()
        );
    }

    /**
     * Tests the ::email() function.
     */
    public function testEmail()
    {
        self::assertMatchesRegularExpression(
            "/([A-Z]|[a-z])+([0-9]+)@([A-Z]|[a-z])+\.([A-Z]|[a-z])+/",
            Faker::email()
        );
    }
}