<?php

namespace Curfle\Tests\Resources\DummyClasses\DAO;

class Phone extends \Curfle\DAO\Model
{

    public int $id;

    /**
     * @param string $number
     */
    public function __construct(public string $number)
    {
    }

    /**
     * @inheritDoc
     */
    static function config(): array
    {
        return [
            "table" => "phone"
        ];
    }
}