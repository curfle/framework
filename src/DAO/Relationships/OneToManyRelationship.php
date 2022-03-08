<?php

namespace Curfle\DAO\Relationships;

use Curfle\DAO\Model;
use Exception;

class OneToManyRelationship extends Relationship
{
    public function __construct(
        private Model  $model,
        private string $targetClass,
        private string $fkColumnInClass
    )
    {
        parent::__construct();
    }

    /**
     * Associate an object from the relationship.
     *
     * @param Model $object
     * @return bool
     * @throws Exception
     */
    function associate(Model $object): bool
    {
        $targetConfig = call_user_func($this->targetClass . "::__getCleanedConfig");
        $success = $this->model::__callTableOnConnector($targetConfig["table"])
            ->where($targetConfig["primaryKey"], $object->primaryKey())
            ->update([
                $this->fkColumnInClass => $this->model->primaryKey()
            ]);

        // set model property
        if($success){
            $modelPropertiesToColumns = array_flip($object->__getCleanedConfig()["fields"]);
            $object->{$modelPropertiesToColumns[$this->fkColumnInClass]} = $this->model->primaryKey();
        }

        return $success;
    }

    /**
     * Dissociates an object from the relationship.
     *
     * @param Model $object
     * @return bool
     * @throws Exception
     */
    function dissociate(Model $object): bool
    {
        $targetConfig = call_user_func($this->targetClass . "::__getCleanedConfig");
        $success = $this->model::__callTableOnConnector($targetConfig["table"])
            ->where($targetConfig["primaryKey"], $object->primaryKey())
            ->update([
                $this->fkColumnInClass => null
            ]);

        // reset model property
        if($success){
            $modelPropertiesToColumns = array_flip($object->__getCleanedConfig()["fields"]);
            $object->{$modelPropertiesToColumns[$this->fkColumnInClass]} = null;
        }

        return $success;
    }

    /**
     * @inheritDoc
     */
    function get(): array
    {
        // check if trashed objects should be taken into account
        if ($this->withTrashed) {
            $statement = call_user_func(
                $this->targetClass . "::withTrashed",
            )->where($this->fkColumnInClass, $this->model->primaryKey());
        } else {
            $statement = call_user_func(
                $this->targetClass . "::where",
                $this->fkColumnInClass, $this->model->primaryKey()
            );
        }

        return $statement->get();
    }

    /**
     * @inheritDoc
     */
    protected function getCacheKey(): string
    {
        return $this->model::class . "|" . $this->model->primaryKey() . "|"
            . $this->targetClass . "|" . $this->fkColumnInClass;
    }
}