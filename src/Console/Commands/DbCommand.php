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
     * The name and the signature of the command.
     *
     * @var string
     */
    protected string $signature = "db {connector?}";

    /**
     * The description of the command.
     *
     * @var string
     */
    protected string $description = "Starts a new database CLI session.";

    /**
     * Execute the console command.
     */
    public function handle(Input $input)
    {
        // load connection from parameter and connect
        $connectorName = $input->argument("connector") ?? "database";
        $connector = DB::connector($input->argument("connector"));
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

        // disconnect from database
        $this->success("$connectorName connection closed");
    }
}