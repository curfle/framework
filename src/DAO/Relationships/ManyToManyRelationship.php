<?php

namespace Curfle\DAO\Relationships;

use Curfle\Agreements\DAO\DAOInterface;
use Curfle\DAO\Model;

class ManyToManyRelationship extends Relationship
{
    public function __construct(
        private Model $model,
        private string $targetClass,
        private string $pivotTable,
        private string $fkColumnOfCurrentModelInPivotTable,
        private string $fkColumnOfOtherModelInPivotTable)
    {
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

        if($object !== null)
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
        $targetClass = $this->targetClass;
        $entries = $this->model::__callTableOnConnector($this->pivotTable)
            ->valueAs($this->fkColumnOfOtherModelInPivotTable, "id")
            ->where($this->fkColumnOfCurrentModelInPivotTable, $this->model->primaryKey())
            ->get();
        return array_map(function($entry) use($targetClass) {
            return call_user_func($targetClass . "::get", $entry["id"]);
        }, $entries);
    }
}