<?php

require_once __DIR__ . "/../vendor/autoload.php";

/**
 * define database constants
 */
const DB_HOST = "127.0.0.1";
const DB_USER = "root";
const DB_PASSWORD = "root";
const DB_DATABASE = "curfle";

const DB_SQLITE_FILENAME = __DIR__ . "/Resources/Database/database.sqlite";

/**
 * Create the .sqlite database file
 */
file_put_contents(DB_SQLITE_FILENAME, "");