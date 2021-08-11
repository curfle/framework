<?php

namespace Curfle\Tests\Resources\DummyClasses\DAO;

use Curfle\DAO\Relationships\ManyToOneRelationship;
use Curfle\DAO\Relationships\OneToOneRelationship;

/**
 * @property-read User $user
 */
class Login extends \Curfle\DAO\Model
{

    public int $id;

    /**
     * @param int|null $user_id
     * @param string|null $timestamp
     */
    public function __construct(
        public ?int    $user_id = null,
        public ?string $timestamp = null
    )
    {
    }

    /**
     * @inheritDoc
     */
    static function config(): array
    {
        return [
            "table" => "login"
        ];
    }

    /**
     * Returns the associated user.
     *
     * @return ManyToOneRelationship
     */
    public function user() : ManyToOneRelationship
    {
        return $this->belongsTo(User::class);
    }
}