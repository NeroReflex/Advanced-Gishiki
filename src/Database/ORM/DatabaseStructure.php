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

namespace Gishiki\Database\ORM;

use Gishiki\Algorithms\Collections\GenericCollection;
use Gishiki\Algorithms\Collections\StackCollection;
use Gishiki\Algorithms\Collections\SerializableCollection;
use Gishiki\Database\DatabaseManager;
use Gishiki\Database\RelationalDatabaseInterface;
use Gishiki\Database\Schema\Column;
use Gishiki\Database\Schema\ColumnType;
use Gishiki\Database\Schema\Table;

/**
 * Build the database logic structure from a json descriptor.
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
final class DatabaseStructure
{
    /**
     * @var string The name of the corresponding connection
     */
    protected $connectionName;

    /**
     * @var StackCollection the collection of tables in creation reversed order
     */
    protected $stackTables;

    /**
     * Build the Database structure from a json text.
     *
     * @param  string $description the json description of the database
     * @throws StructureException the error in the description
     */
    public function __construct($description)
    {
        $this->connectionName = new StackCollection();

        //deserialize the json content
        $deserializedDescr = SerializableCollection::deserialize($description);

        if (!$deserializedDescr->has('connection')) {
            throw new StructureException('A database description must contains the connection field', 0);
        }

        $this->connectionName = $deserializedDescr->get('connection');

        if (!$deserializedDescr->has('tables'))  {
            throw new StructureException('A database description must contains a tables field', 1);
        }

        foreach ($deserializedDescr->get('tables') as $tb) {
            $table = new GenericCollection($tb);

            if (!$table->has('name')) {
                throw new StructureException('Each table must have a name');
            }

            $currentTable = new Table($table->get('name'));

            foreach ($table->get('fields') as $fd) {
                $field = new GenericCollection($fd);

                if (!$field->has('name')) {
                    throw new StructureException('Each column must have a name');
                }

                if (!$field->has('type')) {
                    throw new StructureException('Each column must have a type');
                }

                $typeIdentifier = ColumnType::UNKNOWN;
                switch ($field->get('type')) {
                    case 'string':
                    case 'text':
                        $typeIdentifier = ColumnType::TEXT;
                        break;

                    case 'smallint':
                        $typeIdentifier = ColumnType::SMALLINT;
                        break;

                    case 'int':
                    case 'integer':
                        $typeIdentifier = ColumnType::INTEGER;
                        break;

                    case 'bigint':
                        $typeIdentifier = ColumnType::BIGINT;
                        break;

                    case 'money':
                        $typeIdentifier = ColumnType::MONEY;
                        break;

                    case 'numeric':
                        $typeIdentifier = ColumnType::NUMERIC;
                        break;

                    case 'float':
                        $typeIdentifier = ColumnType::FLOAT;
                        break;

                    case 'double':
                        $typeIdentifier = ColumnType::DOUBLE;
                        break;

                    case 'datetime':
                        $typeIdentifier = ColumnType::DATETIME;
                        break;
                }

                $currentField = new Column($field->get('name'), $typeIdentifier);
                $currentField->setPrimaryKey(($field->get('primary key') === true));
                $currentField->setNotNull(($field->get('not null') === true));
                $currentField->setAutoIncrement(($field->get('autoincrement') === true));

                $currentTable->addColumn($currentField);
            }

            //add the table to the collection
            $this->stackTables->push($currentTable);
        }
    }

    /**
     * Apply the structure to the database.
     */
    public function apply()
    {
        $connection = DatabaseManager::retrieve($this->connectionName);

        if ($connection instanceof RelationalDatabaseInterface) {
            $this->stackTables->reverse();

            while (!$this->stackTables->empty()) {
                $connection->createTable($this->stackTables->pop());
            }
        }
    }
}