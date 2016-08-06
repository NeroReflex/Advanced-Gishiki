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

namespace Gishiki\tests\Pipeline;

use Gishiki\Pipeline\PipelineRuntime;
use Gishiki\Pipeline\Pipeline;
use Gishiki\Algorithms\Collections\GenericCollection;
use Gishiki\Database\DatabaseManager;

/**
 * The tester for the PipelineRuntime class.
 * 
 * @author Benato Denis <benato.denis96@gmail.com>
 */
class PipelineRuntimeTest extends \PHPUnit_Framework_TestCase {
    
    private static function GetConnection()
    {
        try {
            return DatabaseManager::Retrieve('pipeline_testing_db');
        } catch (\Gishiki\Database\DatabaseException $ex) {
            return DatabaseManager::Connect('pipeline_testing_db', \Gishiki\tests\Database\MongoDatabaseTest::GetConnectionQuery());
        }
    }
    
    public function testFullPipeline()
    {
        self::GetConnection();
        \Gishiki\Pipeline\PipelineSupport::Initialize('pipeline_testing_db', 'testing.pipeline');
        
        $pipeline = new Pipeline("first_fulltest!");
        $pipeline->bindStage('firstStage', function (GenericCollection &$collection) {
            $collection->set('value', 0x5F);
        });
        
        //creaate the pipeline runtime
        $pipelineExecutor = new PipelineRuntime($pipeline);
        $pipelineExecutor(2);
        
        $pipelineRuntimeReflected = new \ReflectionObject($pipelineExecutor);
        $serializableCollectionProp = $pipelineRuntimeReflected->getProperty('serializableCollection');
        $serializableCollectionProp->setAccessible(true);
        $serializableCollection = $serializableCollectionProp->getValue($pipelineExecutor);
        
        $this->assertEquals(0x5F, $serializableCollection->get('value'));
    }
}
