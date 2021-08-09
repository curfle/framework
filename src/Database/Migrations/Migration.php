<?php

namespace Curfle\Database\Migrations;

abstract class Migration
{
    /**
     * The database connector that should be used by the migration.
     *
     * @var string|null
     */
    protected ?string $connection = null;

    /**
     * Run the migrations.
     *
     * @return void
     */
    abstract public function up();

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    abstract public function down();
}