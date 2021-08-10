<?php

namespace Curfle\Tests\Resources\DummyClasses\DAO;

class Job extends \Curfle\DAO\Model
{

    public int $id;

    /**
     * @param string|null $name
     */
    public function __construct(public ?string $name=null)
    {
    }

    /**
     * @inheritDoc
     */
    static function config(): array
    {
        return [
            "table" => "job"
        ];
    }
}