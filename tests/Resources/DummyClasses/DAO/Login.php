<?php

namespace Curfle\Tests\Resources\DummyClasses\DAO;

use Curfle\DAO\Model;
use Curfle\DAO\Relationships\ManyToOneRelationship;

/**
 * @property-read User $user
 */
class Login extends Model
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