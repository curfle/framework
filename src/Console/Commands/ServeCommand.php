<?php

namespace Curfle\Console\Commands;

use Closure;
use Curfle\Console\Input;
use Curfle\Essence\Application;
use Curfle\Console\Command;
use Curfle\FileSystem\FileSystem;
use Curfle\Support\Exceptions\FileSystem\FileNotFoundException;
use Curfle\Support\Facades\Buddy;

class ServeCommand extends Command
{
    /**
     * @inheritDoc
     */
    protected function install()
    {
        $this->signature("serve {address?}")
            ->description("Serves the application under ")
            ->resolver(function (Input $input, FileSystem $files) {
                // get serving address or default it to "http://localhost:8080"
                $address = $input->namedArgument("address");
                if($address === "")
                    $address = "localhost:8080";

                // find the server file
                $serverFile = $this->app->basePath("server.php");
                if(!$files->exists($serverFile))
                    throw new FileNotFoundException("The server.php in the projects' base directory could not be found.");

                // inform user
                $this->success("Curfle development server started at: http://$address");
                $this->warning("Press Ctrl-C to exit...");
                $this->flush();

                // start server
                $publicPath = $this->app->publicPath();
                exec("php -S $address -t $publicPath $serverFile &>/dev/null");
            })->where("address", '([a-z]|[A-Z]|[0-9])+\/?(\:([0-9])+)?\/?');
    }
}