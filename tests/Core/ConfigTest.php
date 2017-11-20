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

namespace Gishiki\tests\Core\Router;

use Gishiki\Algorithms\Collections\SerializableCollection as Serializable;

use Gishiki\Algorithms\Collections\SerializableCollection;
use Gishiki\Core\Application;
use Gishiki\Core\Config;
use Gishiki\Core\Exception;
use PHPUnit\Framework\TestCase;

/**
 * The tester for the Config class.
 *
 * Used to test every feature of the config component
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
class ConfigTest extends TestCase
{
    public function testCachedLoading()
    {
        $data = new SerializableCollection([
            "oncache" => false
        ]);

        $filename = 'tests/config_'.__FUNCTION__.'.json';
        file_put_contents($filename, $data->serialize());

        $cache = new \Memcached();
        $cache->addServer("localhost", 11211, 100);

        //clean the cache to have a clean testing environment
        $cache->flush();

        $config = new Config($filename, $cache);

        $cacheKey = sha1($config->getFilename());

        $this->assertEquals(false, $config->getConfiguration()->get("oncache"));

        //change value on cache only :)
        $data->set("oncache", true);
        $cache->set($cacheKey, serialize($data->all()));

        $config = new Config($filename, $cache);

        //make sure settings were loaded from cache
        $this->assertEquals(true, $config->getConfiguration()->get("oncache"));

        //remove the used config file
        unlink($filename);
    }

    public function testBadFileConfig()
    {
        $this->expectException(Exception::class);

        $filename = 'tests/config_'.__FUNCTION__.'.json';

        new Config($filename);
    }

    public function testConfig()
    {
        $filename = 'tests/config_'.__FUNCTION__.'.json';

        $serializedConf = new Serializable([
            "general" => [
                "development" => true
            ]
        ]);

        file_put_contents($filename, $serializedConf->serialize(Serializable::JSON));

        $config = new Config($filename);

        $this->assertEquals(true, $config->getConfiguration()->get("general")["development"]);

        unlink($filename);
    }

    public function testEnvConfig()
    {
        $random = bin2hex(openssl_random_pseudo_bytes(10));
        putenv ( "SERIAL=".$random);

        $filename = 'tests/config_'.__FUNCTION__.'.json';

        $serializedConf = new Serializable([
            "general" => [
                "development" => true
            ],
            "serial" => "{{@SERIAL}}"
        ]);

        file_put_contents($filename, $serializedConf->serialize(Serializable::JSON));

        $config = new Config($filename);

        $this->assertEquals($random, $config->getConfiguration()->get("serial"));

        unlink($filename);
    }

    public function testMacroConfig()
    {
        $random = bin2hex(openssl_random_pseudo_bytes(10));
        define('MACRO', $random);

        $filename = 'tests/config_'.__FUNCTION__.'.json';

        $serializedConf = new Serializable([
            "general" => [
                "development" => true
            ],
            "macro" => "{{@MACRO}}"
        ]);

        file_put_contents($filename, $serializedConf->serialize(Serializable::JSON));

        $config = new Config($filename);

        $this->assertEquals($random, $config->getConfiguration()->get("macro"));

        unlink($filename);
    }
}