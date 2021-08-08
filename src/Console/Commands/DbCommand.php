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
     * @inheritdoc
     */
    protected function install()
    {
        $this->signature("db {connector?}")
            ->where("connector", "([a-z]|[A-Z]|[0-9])+")
            ->description("Starts a new database CLI session")
            ->resolver(function (Input $input) {
                // load connection from parameter and connect
                $connector = DB::connector($input->namedArgument("connector"));
                $connectorName = $input->namedArgument("connector") ?? "database";
                $connector->connect();

                while(true){
                    $query = $this->prompt("$connectorName> ");

                    // exit comdition
                    if(in_array($query, ["exit", "exit;", "quit", "quit;"]))
                        break;

                    try{
                        $result = $connector->rows($query);
                        var_dump($result);
                    }catch (\Exception $e){
                        $this->error($e->getMessage())->flush();
                    }
                }

                // disconnect fro database
                $connector->disconnect();
                $this->success("$connectorName connection closed");
            });
    }
}