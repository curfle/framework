<?php

namespace Curfle\Tests\Resources\DummyClasses\DAO;

use Curfle\DAO\AuthenticatableModel;
use Curfle\DAO\Relationships\ManyToManyRelationship;
use Curfle\DAO\Relationships\OneToManyRelationship;
use Curfle\DAO\Relationships\OneToOneRelationship;

/**
 * @property-read ?Phone $phone
 * @property-read Login[] $logins
 * @property-read Role[] $roles
 */
class User extends AuthenticatableModel
{

    public int $id;
    public ?string $created;

    /**
     * @param string|null $firstname
     * @param string|null $lastname
     * @param string|null $email
     */
    public function __construct(
        public ?string $firstname = null,
        public ?string $lastname = null,
        public ?string $email = null
    )
    {
    }

    /**
     * @inheritDoc
     */
    static function config(): array
    {
        return [
            "table" => "user",
            "softDelete" => true
        ];
    }

    /**
     * Returns the associated phone.
     *
     * @return OneToOneRelationship
     */
    public function phone(): OneToOneRelationship
    {
        return $this->hasOne(Phone::class);
    }

    /**
     * Returns the associated logins.
     *
     * @return OneToManyRelationship
     */
    public function logins(): OneToManyRelationship
    {
        return $this->hasMany(Login::class);
    }

    /**
     * Returns the associated roles.
     *
     * @return ManyToManyRelationship
     */
    public function roles(): ManyToManyRelationship
    {
        return $this->belongsToMany(Role::class, "user_role");
    }
}