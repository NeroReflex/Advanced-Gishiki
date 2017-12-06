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

use Gishiki\Algorithms\Collections\GenericCollection;
use Gishiki\Database\DatabaseInterface;

/**
 * Provides basic implementation of an object that
 * are eligible for CRUD operations inside a database.
 *
 * @see ActiveRecordInterface Documentation.
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
abstract class ActiveRecord extends GenericCollection implements ActiveRecordInterface
{
    use ActiveRecordStructureTrait;
    use ActiveRecordSerializationTrait;

    /**
     * @var array the structure descriptor
     */
    protected static $structure = [];

    /**
     * @var DatabaseInterface|null the database handler
     */
    protected $database = null;


    public function __construct(DatabaseInterface &$connection)
    {
        //store a reference to the database connection
        $this->database = &$connection;

        //enforce loading table definition
        static::getTableDefinition();

        //enforce loading of serialization
        $this->initTransitionSchema();
    }

    public function save()
    {
        //setup the database schema to avoid errors
        static::initSchema($this->database);

        //get data as used from model
        $unfilteredData = $this->all();

        //filter it to be written to the database
        $filteredData = $this->executeSerialization($unfilteredData);

        if (is_null($this->getObjectID())) {
            $this->database->create($this->getCollectionName(), $filteredData);
        }
    }

    public function delete()
    {
        //setup the database schema to avoid errors
        static::initSchema($this->database);

        // TODO: Implement delete() method.
    }

    public function getObjectID()
    {
        // TODO: Implement getObjectID() method.
    }

    public static function load(DatabaseInterface &$connection) : array
    {
        //setup the database schema to avoid errors
        static::initSchema($connection);

        // TODO: Implement load() method.
    }

    public function set($key, $value)
    {
        $filteredValue = $this->executeFilter($key, $value);

        parent::set($key, $filteredValue);
    }

    public function get($key, $default = null)
    {
        return $this->has($key) ? $this->data[$key] : $default;
    }
}
