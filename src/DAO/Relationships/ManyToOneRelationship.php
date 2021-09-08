<?php

namespace Curfle\DAO\Relationships;

use Curfle\Agreements\DAO\DAOInterface;
use Curfle\DAO\Model;

class ManyToOneRelationship extends Relationship
{
    public function __construct(
        private Model  $model,
        private string $targetClass,
        private string $fkColumn,
    )
    {
    }

    /**
     * Associate an object with teh current class.
     *
     * @param Model $object
     * @return bool
     */
    function associate(Model $object): bool
    {
        $config = $this->model::__getCleanedConfig();
        $success = $this->model::__callTableOnConnector($config["table"])
            ->where($config["primaryKey"], $this->model->primaryKey())
            ->update([
                $this->fkColumn => $object->primaryKey()
            ]);

        // set model property
        if($success){
            $modelPropertiesToColumns = array_flip($this->model->__getCleanedConfig()["fields"]);
            $this->model->{$modelPropertiesToColumns[$this->fkColumn] ?? $this->fkColumn} = $object->primaryKey();
        }

        return $success;
    }

    /**
     * Removes the relationship to another object.
     *
     * @return bool
     */
    function dissociate(): bool
    {
        $config = $this->model::__getCleanedConfig();
        $success = $this->model::__callTableOnConnector($config["table"])
            ->where($config["primaryKey"], $this->model->primaryKey())
            ->update([
                $this->fkColumn => null
            ]);

        // reset model property
        if($success){
            $modelPropertiesToColumns = array_flip($this->model->__getCleanedConfig()["fields"]);
            $this->model->{$modelPropertiesToColumns[$this->fkColumn] ?? $this->fkColumn} = null;
        }

        return $success;
    }

    /**
     * @inheritDoc
     */
    function get(): mixed
    {
        $targetConfig = call_user_func($this->targetClass . "::__getCleanedConfig");
        $modelPropertiesToColumns = array_flip($this->model->__getCleanedConfig()["fields"]);
        $item = call_user_func(
            $this->targetClass . "::where",
            $targetConfig["primaryKey"], $this->model->{$modelPropertiesToColumns[$this->fkColumn] ?? $this->fkColumn}
        )->first();
        return call_user_func($this->targetClass . "::__createInstanceFromArray", $item);
    }
}