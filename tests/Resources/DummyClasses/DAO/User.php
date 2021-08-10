<?php

namespace Curfle\Tests\Resources\DummyClasses\DAO;

/**
 * @property-read ?Job $job
 * @property-read Login[] $logins
 * @property-read Role[] $roles
 */
class User extends \Curfle\DAO\Model
{

    public int $id;

    /**
     * @param string|null $firstname
     * @param string|null $lastname
     * @param string|null $email
     * @param int|null $job_id
     * @param string|null $created
     */
    public function __construct(
        public ?string $firstname = null,
        public ?string $lastname = null,
        public ?string $email = null,
        public ?int    $job_id = null,
        public ?string $created = null
    )
    {
    }

    /**
     * @inheritDoc
     */
    static function config(): array
    {
        return [
            "table" => "user"
        ];
    }

    /**
     * Returns the associated job.
     *
     * @return Job|null
     */
    public function job() : ?Job
    {
        return $this->hasOne(Job::class);
    }

    /**
     * Returns the associated logins.
     *
     * @return Login[]
     */
    public function logins() : array
    {
        return $this->hasMany(Login::class);
    }

    /**
     * Returns the associated roles.
     *
     * @return Role[]
     */
    public function roles() : array
    {
        return $this->belongsToMany(Role::class, "user_role");
    }
}