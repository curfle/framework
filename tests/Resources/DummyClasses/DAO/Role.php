<?php

namespace Curfle\Tests\Resources\DummyClasses\DAO;

use Curfle\DAO\Model;
use Curfle\DAO\Relationships\ManyToManyRelationship;

/**
 * @property-read User[] $users
 */
class Role extends Model
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
            "table" => "role",
            "softDelete" => true
        ];
    }

    /**
     * Returns the associated users.
     *
     * @return ManyToManyRelationship
     */
    public function users() : ManyToManyRelationship
    {
        return $this->belongsToMany(User::class, "user_role");
    }
}