<?php

namespace Curfle\Console\Commands;

use Curfle\Console\Command;
use Curfle\Console\Input;
use Exception;

class MakeSecretCommand extends Command
{
    /**
     * The name and the signature of the command.
     *
     * @var string
     */
    protected string $signature = "make:secret {length?}";

    /**
     * The regular expressions of arguments.
     *
     * @var array|string[]
     */
    protected array $where = ["length" => "[0-9]+"];

    /**
     * The description of the command.
     *
     * @var string
     */
    protected string $description = "Generates a new random secret.";

    /**
     * Execute the console command.
     * 
     * @return void
     *
     * @throws Exception
     */
    public function handle(Input $input)
    {
        // get length
        $length = $input->argument("length");
        if ($length === null)
            $length = 32;
        $length = intval($length);
        if ($length <= 0)
            $length = 32;

        // generate secret
        $secret = bin2hex(random_bytes($length));
        $this->success($secret);
        $this->warning("Attention: Keep your secret safe. Do not publish or share this information.");
    }
}