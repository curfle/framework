<?php

namespace Curfle\Console\Commands;

use Curfle\Console\Input;
use Curfle\Console\Command;
use Curfle\FileSystem\FileSystem;
use Curfle\Support\Exceptions\FileSystem\FileNotFoundException;

class ServeCommand extends Command
{
    /**
     * The name and the signature of the command.
     *
     * @var string
     */
    protected string $signature = "serve {address?}";

    /**
     * The regular expressions of arguments.
     *
     * @var array|string[]
     */
    protected array $where = ["address" => "([a-z]|[A-Z]|[0-9])+\/?(\:([0-9])+)?\/?"];

    /**
     * The description of the command.
     *
     * @var string
     */
    protected string $description = "Serves the application under localhost via the inbuilt PHP development server.";

    /**
     * Execute the console command.
     */
    public function handle(Input $input, FileSystem $files) {
        // get serving address or default it to "http://localhost:PORT"
        // where "PORT" is the next free port after 8080.
        $address = $input->argument("address");
        if ($address === null) {
            // find free port
            $port = 8080;
            $foundPort = false;
            while (!$foundPort) {
                $connection = @fsockopen('localhost', (string)$port);
                $foundPort = !is_resource($connection);
                if (!$foundPort)
                    $port++;
            }
            $address = "localhost:$port";
        }

        // find the server file
        $serverFile = $this->app->basePath("server.php");
        if (!$files->exists($serverFile))
            throw new FileNotFoundException("The server.php in the projects' base directory could not be found.");

        // inform user
        $this->success("Starting Curfle development server at: http://$address");
        $this->warning("Press Ctrl-C to exit...");
        $this->flush();

        // start server
        $publicPath = $this->app->publicPath();
        exec("php -S $address -t $publicPath $serverFile &>/dev/null");
    }
}