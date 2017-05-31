<?php
/**************************************************************************
Copyright 2017 Benato Denis

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
 *****************************************************************************/

namespace Gishiki\Database\Adapters\Utils;

/**
 * This utility is useful to create sql queries for PostgreSQL.
 *
 * It extends the SQLQueryBuilder and add PostgreSQL-specific support.
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
final class PostgreSQLQueryBuilder extends SQLQueryBuilder
{
    /**
     * Add (id SEQUENCE PRIMARY KEY NUT NULL, name TEXT NOT NULL, ... ) to the SQL query.
     *
     * @param array $columns a collection of Gishiki\Database\Schema\Column
     *
     * @return \Gishiki\Database\Adapters\Utils\SQLiteQueryBuilder the updated sql builder
     */
    public function &definedAs(array $columns)
    {
        $this->appendToQuery('(');

        $first = true;
        foreach ($columns as $column) {
            if (!$first) {
                $this->appendToQuery(', ');
            }

            $this->appendToQuery($column->getName().' ');

            $typename = '';
            switch ($column->getType()) {

                case ColumnType::TEXT:
                    $typename = 'TEXT';
                    break;

                case ColumnType::DATETIME:
                case ColumnType::INTEGER:
                    $typename = ($column->getAutoIncrement()) ? 'SERIAL' : 'INTEGER';
                    break;

                case ColumnType::REAL:
                    $typename = 'REAL';
                    break;
            }

            $this->appendToQuery($typename.' ');

            if ($column->getPrimaryKey()) {
                $this->appendToQuery('PRIMARY KEY ');
            }

            if ($column->getNotNull()) {
                $this->appendToQuery('NOT NULL');
            }

            if (($relation = $column->getRelation()) != null) {
                $this->appendToQuery(' REFERENCES '.$relation->getForeignTable()->getName().'('.$relation->getForeignKey()->getName().')');
            }

            $first = false;
        }

        $this->appendToQuery(')');

        //chain functions calls
        return $this;
    }
}