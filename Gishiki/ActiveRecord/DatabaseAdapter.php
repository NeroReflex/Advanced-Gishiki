<?php
/**************************************************************************
Copyright 2015 Benato Denis

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

namespace Gishiki\ActiveRecord;

/**
 * This is the interface each database adapter MUST implement
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
interface DatabaseAdapter {
    
    /**
     * Create a database connection to the native database using the 
     * given connection query
     * 
     * @param string $connection_query the connection query
     * @throws ConnectionException the error occurred while creating the connection
     */
    public function __construct($connection_query);

    /**
     * Create a record into the database to insert the given data collection
     * 
     * @param string $collection_name the name of the collection to be filled
     * @param array $collection_values the array of name => value pairs
     * @throws DatabaseException the error preventing the collection to be filled
     * @return mixed the unique ID of the newly created collection
     */
    public function Insert($collection_name, $collection_values);
}