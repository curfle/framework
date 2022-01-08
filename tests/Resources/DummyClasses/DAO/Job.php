<?php

namespace Curfle\Tests\Resources\DummyClasses\DAO;

use Curfle\DAO\Model;
use Curfle\DAO\Relationships\ManyToOneRelationship;

class Job extends Model
{

    public int $id;

    /**
     * @param string|null $name
     */
    public function __construct(public ?string $name = null)
    {
    }

    /**
     * @return ManyToOneRelationship
     */
    public function user(): ManyToOneRelationship
    {
        return $this->belongsTo(User::class);
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