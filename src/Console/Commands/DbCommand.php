<?php

namespace Curfle\Console\Commands;

use Curfle\Console\Input;
use Curfle\Console\Command;
use Curfle\Support\Facades\DB;
use Curfle\Support\Str;
use Exception;

class DbCommand extends Command
{
    /**
     * @inheritDoc
     */
    protected function install()
    {
        $this->signature("db {connector?}")
            ->where("connector", "([a-z]|[A-Z]|[0-9])+")
            ->description("Starts a new database CLI session")
            ->resolver(function (Input $input) {
                // load connection from parameter and connect
                $connectorName = $input->namedArgument("connector") ?? "database";
                $connector = DB::connector($input->namedArgument("connector"));
                $connector->connect();

                while (true) {
                    $query = $this->prompt("$connectorName> ");

                    // exit comdition
                    if (in_array($query, ["exit", "exit;", "quit", "quit;"]))
                        break;

                    try {
                        $result = $connector->rows($query);
                        $content = Str::replace(print_r($result, true), "\n        ", "\n    ");
                        $this->write($content, false);
                        $this->flush();
                    } catch (Exception $e) {
                        $this->error($e->getMessage())->flush();
                    }
                }

                // disconnect fro database
                $connector->disconnect();
                $this->success("$connectorName connection closed");
            });
    }
}