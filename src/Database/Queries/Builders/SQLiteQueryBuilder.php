<?php

namespace Curfle\Database\Queries\Builders;

use Curfle\Support\Arr;
use Curfle\Support\Str;

class SQLiteQueryBuilder extends SQLQueryBuilder
{

    /**
     * @inheritDoc
     */
    protected function buildInsertOperation(): string
    {
        $statement = $this->operation . " ";

        // update on duplicate key
        $statement .= $this->updateOnDuplicateKey ? self::OR_REPLACE . " " : "";

        // ignore
        $statement .= $this->ignoreOnExists ? self::OR_IRGNORE . " " : "";

        // table
        $statement .= self::INTO . " $this->table ";

        // column names
        $statement .= "(" . Str::concat(Arr::keys($this->insertData[0]), ", ") . ") ";

        // values
        $statement .= self::VALUES . " ";
        $statement .= Str::concat(
                Arr::map(
                    $this->insertData,
                    fn($insert) => "(" . Str::concat(Arr::map($insert, fn() => static::getBindParameterName()), ", ") . ")"
                    ),
                ", ") . " ";
        // bind params
        foreach ($this->insertData as $data) {
            foreach ($data as $value) {
                $this->bindParam($value);
            }
        }

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