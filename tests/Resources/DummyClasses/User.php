<?php

namespace Curfle\Tests\Resources\DummyClasses;

class User extends \Curfle\DAO\Model
{

    public int $id;
    public ?string $firstname;
    public ?string $lastname;
    public ?string $email;
    public ?string $created;

    /**
     * @param string|null $firstname
     * @param string|null $lastname
     * @param string|null $email
     * @param string|null $created
     */
    public function __construct(string $firstname=null, string $lastname=null, string $email=null, string $created=null)
    {
        $this->firstname = $firstname;
        $this->lastname = $lastname;
        $this->email = $email;
        $this->created = $created;
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
}