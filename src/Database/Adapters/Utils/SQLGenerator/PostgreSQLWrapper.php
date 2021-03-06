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

use Gishiki\Database\Schema\ColumnType;

/**
 * This utility is useful to create sql queries for PostgreSQL.
 *
 * It extends the SQLQueryBuilder and add PostgreSQL-specific support.
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
final class PostgreSQLWrapper implements SQLWrapperInterface
{
    use SQLWrapper;

    /**
     * Add RETURNING %id% to the SQL query.
     *
     * @param string $idFieldName the name of the table primary key
     *
     * @return PostgreSQLWrapper the updated sql builder
     */
    public function &returning($idFieldName) : PostgreSQLWrapper
    {
        $this->appendToQuery(' RETURNING "'.$idFieldName.'" ');

        //chain functions calls
        return $this;
    }

    /**
     * Add (id sequence PRIMARY KEY, name text NOT NULL, ... ) to the SQL query.
     *
     * @param array $columns a collection of Gishiki\Database\Schema\Column
     *
     * @return SQLWrapperInterface the updated sql builder
     */
    public function &definedAs(array $columns) : SQLWrapperInterface
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
                    $typename = 'text';
                    break;

                case ColumnType::SMALLINT:
                    $typename = ($column->isAutoIncrement()) ? 'serial' : 'smallint';
                    break;

                case ColumnType::DATETIME:
                case ColumnType::INTEGER:
                    $typename = ($column->isAutoIncrement()) ? 'serial' : 'integer';
                    break;

                case ColumnType::BIGINT:
                    $typename = ($column->isAutoIncrement()) ? 'serial' : 'bigint';
                    break;

                case ColumnType::FLOAT:
                    $typename = 'float';
                    break;

                case ColumnType::DOUBLE:
                    $typename = 'double';
                    break;

                case ColumnType::NUMERIC:
                    $typename = 'numeric';
                    break;

                case ColumnType::MONEY:
                    $typename = 'money';
                    break;
            }

            $this->appendToQuery($typename.' ');

            if ($column->isPrimaryKey()) {
                $this->appendToQuery('PRIMARY KEY ');
            }

            if (($column->isNotNull()) && ($typename != 'serial')) {
                $this->appendToQuery('NOT NULL');
            }

            $relation = $column->getRelation();
            if (!is_null($relation)) {
                $this->appendToQuery(' REFERENCES '.$relation->getForeignTable()->getName().'('.$relation->getForeignKey()->getName().')');
            }

            $first = false;
        }

        $this->appendToQuery(')');

        //chain functions calls
        return $this;
    }
}
