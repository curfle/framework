<?php

namespace Curfle\Database\Queries\Builders;

use Curfle\Support\Arr;
use Curfle\Support\Str;

class MySQLQueryBuilder extends SQLQueryBuilder
{

    /**
     * @inheritDoc
     */
    protected function buildInsertOperation(): string
    {

        $statement = $this->operation . " ";

        // ignore
        $statement .= $this->ignoreOnExists ? self::IGNORE . " " : "";

        // table
        $statement .= self::INTO . " $this->table ";

        // column names
        $statement .= "(" . Str::concat(Arr::keys($this->insertData[0]), ", ") . ") ";

        // values
        $statement .= self::VALUES . " ";
        $statement .= Str::concat(
                array_map(
                    fn($insert) => "(" . Str::concat(array_map(fn() => static::getBindParameterName(), $insert), ", ") . ")",
                    $this->insertData),
                ", ") . " ";
        // bind params
        foreach ($this->insertData as $data){
            foreach ($data as $value) {
                $this->bindParam($value);
            }
        }

        // update on duplicate key
        $statement .= $this->updateOnDuplicateKey
            ? self::ON_DUPLICATE_KEY_UPDATE . " " . Str::concat(
                array_map(fn($column) => " $column = " . self::VALUES . "($column)", Arr::keys($this->insertData[0])),
                ", "
            )
            : "";

        return $statement;
    }

    /**
     * @inheritDoc
     */
    protected function getBindParameterName(): string
    {
        return "?";
    }
}