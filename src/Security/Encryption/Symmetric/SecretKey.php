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

namespace Gishiki\Security\Encryption\Symmetric;

use Gishiki\Security\Hashing\Algorithm;
use Gishiki\Security\Hashing\HashingException;

/**
 * This class represents a secret key for the symmetric encryption engine.
 *
 * Note: This class uses OpenSSL for strong encryption
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
final class SecretKey
{
    /**
     * Generate the hexadecimal representation of a secure key
     * using a salt algorithm in order to derive it from the
     * given password.
     *
     * *Note*: once generated the key cannot be retrieved...
     * you __MUST__ save the key for future usage.
     *
     * @param string $password   the password to be derived
     * @param int    $keyLength  the final length of the key (in bytes)
     * @throws \InvalidArgumentException invalid arguments have been passed
     * @throws HashingException          the error occurred while generating the requested hashing algorithm
     * @return string an hex representation of the generated key
     */
    public static function generate($password, $keyLength = 16)
    {
        //generate some random characters
        $salt = openssl_random_pseudo_bytes(2 * $keyLength);

        //generate the pbkdf2 key
        return Algorithm::pbkdf2($password, $salt, $keyLength, 20000, Algorithm::SHA256);
    }

    /**
     * @var string the key in the native format
     */
    private $key;

    /**
     * @var int the key length (in bytes)
     */
    private $keyLength;

    /**
     * Create an encryption key using the given serialized key.
     *
     * A serialized key is the hexadecimal representation of key.
     *
     * You can use the generate() function to retrive a really
     * secure key from the password (the same key derivation
     * algorithm that openssl internally uses).
     *
     * Usage example:
     *
     * <code>
     * //generate a secure pbkdf2-derived key and use it as the encryption key
     * $my_key = new SecretKey(SecretKey::generate("mypassword"));
     *
     * //you MUST save the generated key, because it won't be possible to
     * //generate the same key once again (even using the same password)!
     * $precious_key = (string) $my_key;
     * </code>
     *
     * @param string $key the password to be used in a HEX encoded format
     */
    public function __construct($key)
    {
        //check for the input
        if ((!is_string($key)) || (strlen($key) <= 2)) {
            throw new \InvalidArgumentException('The secure key must be given as a non-empty string that is the hex representation of the real key');
        }

        //get the real encryption key
        $this->keyLength = strlen($key) / 2;
        $this->key = hex2bin($key);
    }

    /**
     * Export the currently loaded key.
     *
     * @return string the hex representation of the loaded key
     */
    public function export()
    {
        return bin2hex($this->key);
    }

    /**
     * Proxy call to the export() function.
     *
     * @return string the serialized key
     */
    public function __toString()
    {
        return $this->export();
    }

    /**
     * Export a reference to the native private key and its length in bits.
     *
     * @return array the array that contains the key and its legth (in bytes)
     */
    public function __invoke()
    {
        //get & return secure key details
        return [
            'key' => $this->key,
            'byteLength' => $this->keyLength,
        ];
    }
}
