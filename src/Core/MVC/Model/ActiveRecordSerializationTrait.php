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

namespace Gishiki\Core\MVC\Model;
use Gishiki\Database\Schema\ColumnType;

/**
 * Provides a working implementation of table schema extractor.
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
trait ActiveRecordSerializationTrait
{
    /**
     * @var array the matrix of transformation of model_entry_key => db_row_name
     */
    private $transformations = [];

    /**
     * @var \Closure[] the collection of function to be executed to cast each model_entry_key in a correct value
     */
    private $filters = [];

    /**
     * @var string the name of the table/collection that will hold current model data
     */
    private $collection = "";

    /**
     * @var string the name (model wise) of the primary key (if any)
     */
    private $primaryKey = "";

    /**
     * Get the name of the primary key field.
     *
     * @return string the name of the primary key
     */
    private function getPrimaryKeyName() : string
    {
        return $this->primaryKey;
    }

    /**
     * Get the name of the table or collection that will hold data.
     *
     * @return string the collection name
     */
    private function getCollectionName() : string
    {
        return $this->collection;
    }

    /**
     * Load filtering functions from static::$structure array.
     *
     * @throws ActiveRecordException workflow problem
     */
    private function initTransitionSchema()
    {
        //table must have been already correctly parsed, so I know data is well-formed
        if (!ActiveRecordTables::isRegistered(static::class)) {
            throw new ActiveRecordException("Table definition for the current ActiveRecord object is missing.", 300);
        }

        foreach (static::$structure['fields'] as $fieldName => &$fieldDefinition) {
            $this->transformations[$fieldName] = $fieldDefinition["name"];
        }

        $table = ActiveRecordTables::retrieve(static::class);

        //update the table name
        $this->collection = $table->getName();
        foreach ($table->getColumns() as &$column) {

            //attempt to register it as the primary key
            $this->primaryKey = ($column->isPrimaryKey()) ? $column->getName() : $this->primaryKey;

            $dataType = $column->getType();

            //the filter does nothing by default
            $filter = function ($value) {
                return $value;
            };
            switch ($dataType) {
                case ColumnType::BIGINT:
                case ColumnType::INTEGER:
                case ColumnType::SMALLINT:
                    $filter = function ($value) {
                        return intval($value);
                    };
                    break;

                case ColumnType::DOUBLE:
                case ColumnType::FLOAT:
                case ColumnType::NUMERIC:
                case ColumnType::MONEY:
                    $filter = function ($value) {
                        return floatval($value);
                    };
                    break;

                case ColumnType::TEXT:
                    $filter = function ($value) {
                        return "$value";
                    };
                    break;
            }

            $targetField = $column->getName();
            $this->filters[$targetField][] = $filter;
        }
    }

    /**
     * Apply data filters on the given data.
     *
     * @param array $data the data to be filtered
     * @return array the filtered data, ready to be written to the database
     */
    private function executeFilters(array $data)
    {
        $filtered = [];

        foreach ($data as $key => $value) {
            $filtered[$key] = $this->executeFilter($key, $value);
        }

        return $filtered;
    }

    /**
     * Perform every necessary filtering value on a given model portion.
     *
     * @param  string $key   the key associated with the given value
     * @param  mixed  $value the value to be filtered
     * @return mixed the filtered value
     */
    private function executeFilter($key, $value)
    {
        $filtered = $value;

        foreach ($this->filters[$key] as $filterNumber => $filter) {
            $filtered = $filter($filtered);
        }

        return $filtered;
    }
}