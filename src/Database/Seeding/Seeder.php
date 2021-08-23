<?php

namespace Curfle\Database\Seeding;

abstract class Seeder
{
    /**
     * Runs the seeder.
     */
    abstract public function run(): void;
}