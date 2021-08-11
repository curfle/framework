<?php

namespace Curfle\DAO\Relationships;

use Curfle\Agreements\DAO\DAOInterface;
use Curfle\DAO\Model;

class OneToOneRelationship extends Relationship
{
    public function __construct(
        private Model  $model,
        private string $targetClass,
        private string $fkColumnInClass
    )
    {
    }

    /**
     * Sets an object as the relationship.
     *
     * @param Model $object
     * @return bool
     */
    function set(Model $object): bool
    {
        $this->model->{$this->fkColumnInClass} = $object->primaryKey();
        return $this->model->update();
    }

    /**
     * Sets an object as the relationship.
     *
     * @return bool
     */
    function detach(): bool
    {
        $this->model->{$this->fkColumnInClass} = null;
        return $this->model->update();
    }

    /**
     * @inheritDoc
     */
    function get(): mixed
    {
        return call_user_func($this->targetClass . "::get", $this->model->{$this->fkColumnInClass});
    }
}