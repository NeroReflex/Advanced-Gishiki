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

namespace Gishiki\tests\Security\Encryption\Symmetric;

use Gishiki\Security\Encryption\Symmetric\SecretKey;
use Gishiki\Security\Encryption\Symmetric\Cryptography;

/**
 * Various tests for encryption algorithms.
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
class CriptographyTest extends \PHPUnit_Framework_TestCase
{
    public function testEncryption()
    {
        //generate the key
        $key = new SecretKey(SecretKey::generate('testing/key'));

        $message = 'you should hide this, lol!';

        //encrypt the message
        $enc_message = Cryptography::encrypt($key, $message);

        //decrypt the message
        $result = Cryptography::decrypt($key, $enc_message['Encryption'], $enc_message['IV_base64']);

        //test the result
        $this->assertEquals($message, $result);
    }

    public function testLongEncryption()
    {
        //generate the key
        $key = new SecretKey(SecretKey::generate('testing/key'));

        $message = base64_encode(openssl_random_pseudo_bytes(515));

        //encrypt the message
        $enc_message = Cryptography::encrypt($key, $message);

        //decrypt the message
        $result = Cryptography::decrypt($key, $enc_message['Encryption'], $enc_message['IV_base64']);

        //test the result
        $this->assertEquals($message, $result);
    }
}
