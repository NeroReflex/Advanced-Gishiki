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

namespace Gishiki\tests\Core\MVC\Model;

use Gishiki\Core\MVC\Model\ActiveRecordException;

use Gishiki\Core\MVC\Model\ActiveRecordTables;
use PHPUnit\Framework\TestCase;

/**
 * The tester for the ActiveRecord class.
 *
 * Used to test every feature of the ActiveRecord class
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
class ActiveRecordTest extends TestCase
{
    public function testSchemaWithNoName()
    {
        $this->expectException(ActiveRecordException::class);
        $this->expectExceptionCode(100);

        $reflectedRecord = new \ReflectionClass(\TModelNoTableName::class);
        $reflectedMethod = $reflectedRecord->getMethod("getTableDefinition");
        $reflectedMethod->setAccessible(true);
        $reflectedMethod->invoke(null);

        $this->assertFalse(ActiveRecordTables::isRegistered(\TModelNoTableName::class));
    }

    public function testSchemaWithNoFields()
    {
        $this->expectException(ActiveRecordException::class);
        $this->expectExceptionCode(104);

        $reflectedRecord = new \ReflectionClass(\TModelNoFields::class);
        $reflectedMethod = $reflectedRecord->getMethod("getTableDefinition");
        $reflectedMethod->setAccessible(true);
        $reflectedMethod->invoke(null);

        $this->assertFalse(ActiveRecordTables::isRegistered(\TModelNoFields::class));
    }

    public function testSchemaWithNoFieldName()
    {
        $this->expectException(ActiveRecordException::class);
        $this->expectExceptionCode(101);

        $reflectedRecord = new \ReflectionClass(\TModelNoFieldName::class);
        $reflectedMethod = $reflectedRecord->getMethod("getTableDefinition");
        $reflectedMethod->setAccessible(true);
        $reflectedMethod->invoke(null);

        $this->assertFalse(ActiveRecordTables::isRegistered(\TModelNoFieldName::class));
    }

    public function testSchemaWithNoFieldType()
    {
        $this->expectException(ActiveRecordException::class);
        $this->expectExceptionCode(102);

        $reflectedRecord = new \ReflectionClass(\TModelNoFieldType::class);
        $reflectedMethod = $reflectedRecord->getMethod("getTableDefinition");
        $reflectedMethod->setAccessible(true);
        $reflectedMethod->invoke(null);

        $this->assertFalse(ActiveRecordTables::isRegistered(\TModelNoFieldType::class));
    }

    public function testSchemaWithBadFieldType()
    {
        $this->expectException(ActiveRecordException::class);
        $this->expectExceptionCode(103);

        $reflectedRecord = new \ReflectionClass(\TModelBadFieldType::class);
        $reflectedMethod = $reflectedRecord->getMethod("getTableDefinition");
        $reflectedMethod->setAccessible(true);
        $reflectedMethod->invoke(null);

        $this->assertFalse(ActiveRecordTables::isRegistered(\TModelBadFieldType::class));
    }

    public function testCorrectSchemaWithNoRelations()
    {
        $reflectedRecord = new \ReflectionClass(\TModelCorrectNoRelations::class);
        $reflectedMethod = $reflectedRecord->getMethod("getTableDefinition");
        $reflectedMethod->setAccessible(true);
        $reflectedMethod->invoke(null);

        $this->assertTrue(ActiveRecordTables::isRegistered(\TModelCorrectNoRelations::class));
    }
}