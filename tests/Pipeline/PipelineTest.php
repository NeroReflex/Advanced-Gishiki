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

use Gishiki\Pipeline\Pipeline;
use Gishiki\Algorithms\Collections\SerializableCollection;
use Gishiki\Algorithms\Collections\GenericCollection;

/**
 * The tester for the Pipeline class.
 * 
 * @author Benato Denis <benato.denis96@gmail.com>
 */
class PipelineTest extends \PHPUnit_Framework_TestCase
{
    public function testPipelineStageBinding()
    {
	//setup a simple example pipeline
        $pipeline = new Pipeline("testingPLLN");
        $pipeline->bindStage("setup", function($input) {
            return $input++;
        });
        $pipeline->bindStage("calculate", function($input) {
            return ($input % 2) == 0;
        });
		
        //and additional info
        $this->assertEquals(2, $pipeline->countStages());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testBadPipelineName()
    {
        $pipeline = new Pipeline(3);
    }
	
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testBadFunctionName()
    {
        //setup a simple example pipeline
        $pipeline = new Pipeline("testingPLLNBadFuncName");
        $pipeline->bindStage("", function($input) {
            return $input++;
        });
    }
	
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testBadFunction()
    {
        //setup a simple example pipeline
        $pipeline = new Pipeline("testingPLLNBadFunc");
        $pipeline->bindStage("correct_name", 90);
    }
	
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testDoubleFunctionName()
    {
        //setup a simple example pipeline
        $pipeline = new Pipeline("testingPLLNDoubleFuncName");
        $pipeline->bindStage("same_name", function($input) {
            return $input++;
        });
        $pipeline->bindStage("same_name", function($input) {
            return $input--;
        });
    }
}
