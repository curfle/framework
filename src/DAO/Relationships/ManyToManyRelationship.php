<?php

namespace Curfle\DAO\Relationships;

use Curfle\Agreements\DAO\DAOInterface;
use Curfle\DAO\Model;

class ManyToManyRelationship extends Relationship
{
    public function __construct(
        private Model  $model,
        private string $targetClass,
        private string $pivotTable,
        private string $fkColumnOfCurrentModelInPivotTable,
        private string $fkColumnOfOtherModelInPivotTable)
    {
        parent::__construct();
    }

    /**
     * Attaches an objects to the relationship.
     *
     * @param Model $object
     * @return $this
     */
    function attach(Model $object): bool
    {
        return $this->model::__callTableOnConnector($this->pivotTable)
            ->insert([
                $this->fkColumnOfCurrentModelInPivotTable => $this->model->primaryKey(),
                $this->fkColumnOfOtherModelInPivotTable => $object->primaryKey()
            ]);
    }

    /**
     * Detaches an or all objects from the relationship.
     *
     * @param Model|null $object
     * @return bool
     */
    function detach(Model $object = null): bool
    {
        $statement = $this->model::__callTableOnConnector($this->pivotTable);

        if ($object !== null)
            $statement = $statement->where($this->fkColumnOfOtherModelInPivotTable, $object->primaryKey());

        return $statement
            ->where($this->fkColumnOfCurrentModelInPivotTable, $this->model->primaryKey())
            ->delete();
    }

    /**
     * @inheritDoc
     */
    function get(): array
    {
        $modelConfig = $this->model::__getCleanedConfig();
        $targetConfig = $this->targetClass::__getCleanedConfig();

        $entries = $this->model::__callTableOnConnector($this->pivotTable)
            ->value("{$targetConfig["table"]}.*")
            ->leftJoin(
                $targetConfig["table"],
                "{$this->pivotTable}.{$this->fkColumnOfOtherModelInPivotTable}",
                "=",
                "{$targetConfig["table"]}.{$targetConfig["primaryKey"]}",
            )
            ->where($this->fkColumnOfCurrentModelInPivotTable, $this->model->primaryKey())
            ->orderBy("{$this->pivotTable}.id", "ASC");

        if ($targetConfig["softDelete"])
            $entries->where("deleted", null);

        $entries = $entries->get();

        $targetClass = $this->targetClass;
        return array_map(function ($entry) use ($targetClass) {
            return call_user_func($targetClass . "::__createInstanceFromArray", $entry);
        }, $entries);
    }

    /**
     * @inheritDoc
     */
    protected function getCacheKey(): string
    {
        return $this->model::class . "|" . $this->model->primaryKey() . "|" . $this->targetClass . "|"
            . $this->pivotTable . "|" . $this->fkColumnOfCurrentModelInPivotTable . "|"
            . $this->fkColumnOfOtherModelInPivotTable;
    }
}