<?php

namespace Curfle\DAO\Relationships;

use Curfle\DAO\Model;
use Exception;

class OneToOneRelationship extends Relationship
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
     * Sets an object as the relationship.
     *
     * @param Model $object
     * @return bool
     * @throws Exception
     */
    function set(Model $object): bool
    {
        $targetConfig = call_user_func($this->targetClass . "::__getCleanedConfig");
        $success = $this->model::__callTableOnConnector($targetConfig["table"])
            ->where($targetConfig["primaryKey"], $object->primaryKey())
            ->update([
                $this->fkColumnInClass => $this->model->primaryKey()
            ]);

        // set model property
        if ($success) {
            $modelPropertiesToColumns = array_flip($object->__getCleanedConfig()["fields"]);
            $object->{$modelPropertiesToColumns[$this->fkColumnInClass] ?? $this->fkColumnInClass} = $this->model->primaryKey();
        }

        return $success;
    }

    /**
     * Sets an object as the relationship.
     *
     * @return bool
     */
    function detach(): bool
    {
        $targetConfig = call_user_func($this->targetClass . "::__getCleanedConfig");
        return $this->model::__callTableOnConnector($targetConfig["table"])
            ->where($this->fkColumnInClass, $this->model->primaryKey())
            ->update([
                $this->fkColumnInClass => null
            ]);
    }

    /**
     * @inheritDoc
     */
    function get(): mixed
    {
        return call_user_func(
            $this->targetClass . "::where",
            $this->fkColumnInClass, $this->model->primaryKey()
        )->first();
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