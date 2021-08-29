<?php

namespace Curfle\Console\Commands;

use Closure;
use Curfle\Console\Input;
use Curfle\Essence\Application;
use Curfle\Console\Command;
use Curfle\Support\Facades\Buddy;
use Curfle\Support\Facades\DB;

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
                        $content = str_replace("\n        ", "\n    ", print_r($result, true));
                        $this->write($content, false);
                        $this->flush();
                    } catch (\Exception $e) {
                        $this->error($e->getMessage())->flush();
                    }
                }

                // disconnect fro database
                $connector->disconnect();
                $this->success("$connectorName connection closed");
            });
    }
}