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

namespace Gishiki\Database\Schema;

use Gishiki\Database\DatabaseException;

/**
 * Represent a table inside a database.
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
final class Table
{
    /**
     * @var string the name of the table
     */
    protected $name;

    /**
     * @var array a list of columns inside the current database
     */
    protected $columns;

    /**
     * Initialize a table with the given name.
     * This function internally calls setName(), and you should catch
     * exceptions thrown by that function.
     *
     * @param string $name the name of the table
     */
    public function __construct($name)
    {
        $this->name = '';
        $this->columns = [];
        $this->setName($name);
    }

    /**
     * Add a column to the current table.
     *
     * @param Column $col the column to be added
     * @return Table a reference to the modified table
     * @throws DatabaseException A table with the same name already exists
     */
    public function &addColumn(Column &$col) : Table
    {
        foreach ($this->columns as $currentCol) {
            if (strcmp($col->getName(), $currentCol->getName()) == 0) {
                throw new DatabaseException('A Table cannot contain two columns with the same name', 140);
            }
        }

        $this->columns[] = $col;

        return $this;
    }

    /**
     * Return the list of columns inside the current table.
     *
     * @return Column[] the list of columns
     */
    public function &getColumns() : array
    {
        return $this->columns;
    }

    /**
     * Change the name of the current table.
     *
     * @param string $name the name of the table
     *
     * @return Table a reference to the modified table
     *
     * @throws \InvalidArgumentException the table name is invalid
     */
    public function &setName($name) : Table
    {
        //avoid bad names
        if ((!is_string($name)) || (strlen($name) <= 0)) {
            throw new \InvalidArgumentException('The name of a table must be expressed as a non-empty string');
        }

        $this->name = $name;

        return $this;
    }

    /**
     * Retrieve the name of the table.
     *
     * @return string the table name
     */
    public function getName() : string
    {
        return $this->name;
    }
}
