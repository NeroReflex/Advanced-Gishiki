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

namespace Gishiki\tests\Database\Runtime;

use PHPUnit\Framework\TestCase;

use Gishiki\Database\Runtime\ResultModifier;
use Gishiki\Database\Runtime\FieldOrdering;

/**
 * The tester for the ResultModifier class.
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
class ResultModifierTest extends TestCase
{
    public function testBadInitializer()
    {
        $this->expectException(\InvalidArgumentException::class);
        ResultModifier::initialize('only array and null are allowed here!');
    }

    public function testBadLimit()
    {
        $this->expectException(\InvalidArgumentException::class);
        ResultModifier::initialize([
            'limit' => 5,
            'skip' => 8,
        ])->limit('bad')->skip(5);
    }

    public function testBadOffset()
    {
        $this->expectException(\InvalidArgumentException::class);
        ResultModifier::initialize([
            'limit' => 5,
            'skip' => 8,
        ])->limit(10)->skip('bad');
    }

    public function testBadNameOrdering()
    {
        $this->expectException(\InvalidArgumentException::class);
        ResultModifier::initialize([
            'limit' => 5,
            'skip' => 8,
        ])->order(null, FieldOrdering::ASC);
    }

    public function testBadOrderOrdering()
    {
        $this->expectException(\InvalidArgumentException::class);
        ResultModifier::initialize([
            'limit' => 5,
            'skip' => 8,
        ])->order('name', null);
    }

    public function testLimitAndOffset()
    {
        $exportResult = [
            'limit' => 8,
            'skip' => 5,
            'order' => [],
        ];

        $resMod = ResultModifier::initialize([
            'limit' => 5,
            'skip' => 8,
        ])->limit(8)->skip(5);

        $exportMethod = new \ReflectionMethod($resMod, 'export');
        $exportMethod->setAccessible(true);

        $this->assertEquals($exportResult, $exportMethod->invoke($resMod));
    }

    public function testOrdering()
    {
        $exportResult = [
            'limit' => 0,
            'skip' => 0,
            'order' => [
                'name' => FieldOrdering::ASC,
                'surname' => FieldOrdering::ASC,
                'year' => FieldOrdering::DESC,
            ],
        ];

        $resMod = ResultModifier::initialize([])
                ->order('name', FieldOrdering::ASC)
                ->order('surname', FieldOrdering::ASC)
                ->order('year', FieldOrdering::DESC);

        $exportMethod = new \ReflectionMethod($resMod, 'export');
        $exportMethod->setAccessible(true);

        $this->assertEquals($exportResult, $exportMethod->invoke($resMod));
    }

    public function testOrderingOnInitializer()
    {
        $exportResult = [
            'limit' => 0,
            'skip' => 0,
            'order' => [
                'name' => FieldOrdering::ASC,
                'surname' => FieldOrdering::ASC,
                'year' => FieldOrdering::DESC,
            ],
        ];

        $resMod = ResultModifier::initialize([
            'name' => FieldOrdering::ASC,
            'surname' => FieldOrdering::ASC,
            'year' => FieldOrdering::DESC,
        ]);

        $exportMethod = new \ReflectionMethod($resMod, 'export');
        $exportMethod->setAccessible(true);

        $this->assertEquals($exportResult, $exportMethod->invoke($resMod));
    }
}
