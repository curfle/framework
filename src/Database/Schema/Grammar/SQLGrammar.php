<?php

namespace Curfle\Database\Schema\Grammar;

use Curfle\Agreements\Database\Schema\Grammar\Grammar as GrammarAgreement;
use Curfle\Database\Schema\Blueprint;
use Curfle\Database\Schema\BuilderColumn;
use Curfle\Support\Exceptions\Misc\GuessException;
use Curfle\Support\Str;

abstract class SQLGrammar implements GrammarAgreement
{

    /**
     * Bootstraps the blueprint.
     *
     * @param Blueprint $blueprint
     * @return void
     */
    protected function boostrapBlueprint(Blueprint $blueprint)
    {
        $this->guessForeignKeyColumns($blueprint);
    }

    /**
     * Guesses the foreign key colums that have not been set.
     *
     * @param Blueprint $blueprint
     * @return void
     * @throws GuessException
     */
    protected function guessForeignKeyColumns(Blueprint $blueprint)
    {
        // default all foreign keys' columns to the found primary column if they do not have one
        foreach ($blueprint->getForeignKeys() as $foreignKey) {
            if ($foreignKey->getColumn() === null) {
                // assuming format FK_referenceTable_primarykeyTable
                $referenceTable = Str::split($foreignKey->getName(), "_")[1] ?? null;

                if ($referenceTable === null)
                    throw new GuessException("Could not extract reference table name from foreign key name [{$foreignKey->getName()}], assuming format [FK_referenceTable_primarykeyTable]");

                // find primary column
                $primaryColumn = array_values(array_filter($blueprint->getColumns(), function (BuilderColumn $column) use ($referenceTable) {
                        return in_array($column->getName(), [$referenceTable . "_id", $referenceTable . "Id"]);
                    }))[0] ?? null;

                if ($primaryColumn === null)
                    throw new GuessException("Could not find column [{$referenceTable}(_id|Id)] - specify column on foreign key via ->column('...')");

                $foreignKey->column($primaryColumn->getName());
            }
        }
    }
}