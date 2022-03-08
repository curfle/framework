<?php

namespace Curfle\DAO\Relationships;

use Curfle\DAO\Model;
use Exception;

class ManyToOneRelationship extends Relationship
{
    public function __construct(
        private Model  $model,
        private string $targetClass,
        private string $fkColumn,
    )
    {
        parent::__construct();
    }

    /**
     * Associate an object with teh current class.
     *
     * @param Model $object
     * @return bool
     * @throws Exception
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
     * @throws Exception
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
     * @throws Exception
     */
    function get(): mixed
    {
        $targetConfig = call_user_func($this->targetClass . "::__getCleanedConfig");
        $modelPropertiesToColumns = array_flip($this->model->__getCleanedConfig()["fields"]);

        // get primary column and key of target class
        $targetPkColumn = $targetConfig["primaryKey"];
        $targetPkValue = $this->model->{$modelPropertiesToColumns[$this->fkColumn] ?? $this->fkColumn};

        // check if trashed objects should be taken into account
        if ($this->withTrashed) {
            $statement = call_user_func(
                $this->targetClass . "::withTrashed",
            )->where($targetPkColumn, $targetPkValue);
        } else {
            $statement = call_user_func(
                $this->targetClass . "::where",
                $targetPkColumn, $targetPkValue
            );
        }

        return $statement->first();
    }

    /**
     * @inheritDoc
     */
    protected function getCacheKey(): string
    {
        return $this->model::class . "|" . $this->model->primaryKey() . "|"
            . $this->targetClass . "|" . $this->fkColumn;
    }
}