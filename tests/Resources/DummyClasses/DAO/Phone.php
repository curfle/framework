<?php

namespace Curfle\Tests\Resources\DummyClasses\DAO;

use Curfle\DAO\Model;

class Phone extends Model
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
            "table" => "phone",
            "softDelete" => true
        ];
    }
}