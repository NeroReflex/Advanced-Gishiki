<?php
/**************************************************************************
Copyright 2016 Benato Denis

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

namespace Gishiki\tests\Database;

use Gishiki\Database\DatabaseManager;

/**
 * The tester for the DatabaseManager class.
 * 
 * @author Benato Denis <benato.denis96@gmail.com>
 */
class DatabaseManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testBadConnectionQuery()
    {
        DatabaseManager::Connect(3, 'mongodb://user:pass@host:port/db');
    }

    /**
     * @expectedException \Gishiki\Database\DatabaseException
     */
    public function testConnectionQuery()
    {
        DatabaseManager::Connect('default', 'unknown_db_adapter://user:pass@host:port/db');
    }

    public function testConnection()
    {
        $connection = DatabaseManager::Connect('testing_db', MongoDatabaseTest::GetConnectionQuery());
        $this->assertEquals(true, $connection->Connected());
    }

    /**
     * @expectedException \Gishiki\Database\DatabaseException
     */
    public function testVoidConnection()
    {
        DatabaseManager::Retrieve('testing_bad_db (unconnected)');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidNameConnection()
    {
        DatabaseManager::Retrieve(3);
    }
}
