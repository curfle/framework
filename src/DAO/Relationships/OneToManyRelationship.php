<?php

namespace Curfle\DAO\Relationships;

use Curfle\Agreements\DAO\DAOInterface;
use Curfle\DAO\Model;

class OneToManyRelationship extends Relationship
{
    public function __construct(
        private Model  $model,
        private string $targetClass,
        private string $fkColumnInClass
    )
    {
    }

    /**
     * Associate an object from the relationship.
     *
     * @param Model $object
     * @return bool
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
    function get(): mixed
    {
        $items = call_user_func(
            $this->targetClass . "::where",
            $this->fkColumnInClass, $this->model->primaryKey()
        )->get();
        return array_map(function($item){
            return call_user_func($this->targetClass . "::__createInstanceFromArray", $item);
        }, $items);
    }
}