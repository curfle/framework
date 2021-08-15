<?php

namespace Curfle\Console\Commands;

use Curfle\Console\Command;
use Curfle\Console\Input;

class MakeSecretCommand extends Command
{

    /**
     * @inheritDoc
     */
    protected function install()
    {
        $this->signature("make:secret {length?}")
            ->where("length", "[0-9]*")
            ->description("Creates a new secret")
            ->resolver(function (Input $input) {
                // get length
                $length = $input->namedArgument("length");
                if($length === null)
                    $length = 32;
                $length = intval($length);
                if($length <= 0)
                    $length = 32;

                // generate secret
                $secret = bin2hex(random_bytes($length));
                $this->write("Secret:");
                $this->success($secret);
                $this->write("Copy this secret to the .env file like this:");
                $this->write("SECRET=$secret");
                $this->warning("ATTENTION: Keep your secret safe. Do not publish or share this information.");
            });
    }
}