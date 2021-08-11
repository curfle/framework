<?php

namespace Curfle\Tests\Resources\DummyClasses\DAO;

use Curfle\DAO\Relationships\ManyToManyRelationship;
use Curfle\DAO\Relationships\OneToManyRelationship;
use Curfle\DAO\Relationships\OneToOneRelationship;

/**
 * @property-read ?Job $job
 * @property-read Login[] $logins
 * @property-read Role[] $roles
 */
class User extends \Curfle\DAO\Model
{

    public int $id;
    public ?string $created;

    /**
     * @param string|null $firstname
     * @param string|null $lastname
     * @param string|null $email
     * @param int|null $job_id
     */
    public function __construct(
        public ?string $firstname = null,
        public ?string $lastname = null,
        public ?string $email = null,
        public ?int    $job_id = null
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
     * Returns the associated job.
     *
     * @return OneToOneRelationship
     */
    public function job() : OneToOneRelationship
    {
        return $this->hasOne(Job::class);
    }

    /**
     * Returns the associated logins.
     *
     * @return OneToManyRelationship
     */
    public function logins() : OneToManyRelationship
    {
        return $this->hasMany(Login::class);
    }

    /**
     * Returns the associated roles.
     *
     * @return ManyToManyRelationship
     */
    public function roles() : ManyToManyRelationship
    {
        return $this->belongsToMany(Role::class, "user_role");
    }
}