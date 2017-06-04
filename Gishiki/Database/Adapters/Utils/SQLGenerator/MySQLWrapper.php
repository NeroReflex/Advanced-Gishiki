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

namespace Gishiki\Database\Adapters\Utils\SQLGenerator;

use Gishiki\Database\Schema\Table;
use Gishiki\Database\Schema\ColumnType;

/**
 * This utility is useful to create sql queries for MySQL.
 *
 * It extends the SQLQueryBuilder and add MySQL-specific support.
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
final class MySQLWrapper extends GenericSQL
{
    /**
     * Add (id INTEGER PRIMARY KEY NUT NULL, name TEXT NOT NULL, ... ) to the SQL query.
     *
     * @param array $columns a collection of Gishiki\Database\Schema\Column
     *
     * @return \Gishiki\Database\Adapters\Utils\SQLGenerator\MySQLWrapper the updated sql builder
     */
    public function &definedAs(array $columns)
    {
        //mysql wants PRIMARY KEY(...) after column definitions
        $primaryKeyName = '';

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
                    $typename = 'INTEGER';
                    break;

                case ColumnType::SMALLINT:
                    $typename = 'SMALLINT';
                    break;

                case ColumnType::INTEGER:
                    $typename = 'INTEGER';
                    break;

                case ColumnType::BIGINT;
                    $typename = 'BIGINT';
                    break;

                case ColumnType::FLOAT:
                    $typename = 'FLOAT';
                    break;

                case ColumnType::DOUBLE:
                case ColumnType::MONEY:
                case ColumnType::NUMERIC:
                    $typename = 'DOUBLE';
                    break;
            }

            $this->appendToQuery($typename.' ');

            if ($column->getPrimaryKey()) {
                $primaryKeyName = $column->getName();
            }

            if ($column->getAutoIncrement()) {
                $this->appendToQuery('AUTO_INCREMENT ');
            }

            if ($column->getNotNull()) {
                $this->appendToQuery('NOT NULL');
            }

            if (($relation = $column->getRelation()) != null) {
                $this->appendToQuery(', FOREIGN KEY ('.$column->getName().') REFERENCES '.$relation->getForeignTable()->getName().'('.$relation->getForeignKey()->getName().')');
            }

            $first = false;
        }

        if (strlen($primaryKeyName) > 0) {
            $this->appendToQuery(', PRIMARY KEY ('.$primaryKeyName.')');
        }

        $this->appendToQuery(')');

        //chain functions calls
        return $this;
    }
}