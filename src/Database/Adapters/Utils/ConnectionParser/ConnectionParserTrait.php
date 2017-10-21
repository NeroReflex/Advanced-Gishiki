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

namespace Gishiki\Database\Adapters\Utils\ConnectionParser;

/**
 * Implements a working connection string parser.
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
trait ConnectionParserTrait
{
    /**
     * @var string the database host
     */
    protected $host = null;

    /**
     * @var string the database port
     */
    protected $port = null;

    /**
     * @var string the database name
     */
    protected $name = null;

    /**
     * @var string the database user
     */
    protected $user = null;

    /**
     * @var string the user password
     */
    protected $password = null;

    /**
     * @var array|null the arguments in pdo format
     */
    protected $args = null;

    public function parse($connection)
    {
        if (!is_string($connection)) {
            throw new \InvalidArgumentException("the connection query must be given as a string");
        }

        if (strpos($connection, '@') !== false) {
            $this->parseStandardConnectionQuery($connection);
            return;
        } elseif (strpos($connection, '=') !== false) {
            $this->parsePDOConnectionQuery($connection);
            return;
        }

        throw new ConnectionParserException("the given connection query is not valid", 0);
    }

    /**
     * Parse a connection string given in PDO format (without dbtype://).
     *
     * @param  string $connection the connection string to be parsed
     * @throws ConnectionParserException the error preventing the parse
     * @throws \InvalidArgumentException the connection parameter is not a valid string
     */
    protected function parsePDOConnectionQuery($connection)
    {
        $rawParams = [];
        $paramSplit = explode(';', $connection);

        foreach ($paramSplit as &$param) {
            if (strlen($param) <= 0) {
                continue;
            }

            if (strpos($param, '=') === false) {
                throw new ConnectionParserException("Invalid PDO parameter: ".$param, 1);
            }

            $exploded = explode('=', $param, 2);
            $rawParams[$exploded[0]] = $exploded[1];
        }

        foreach ($rawParams as $key => $value) {
            switch (strtolower($key)) {
                case 'host':
                    $this->host = $value;
                    break;

                case 'port':
                    $this->port = $value;
                    break;

                case 'dbname':
                    $this->name = $value;
                    break;

                case 'user':
                    $this->user = $value;
                    break;

                case 'password':
                    $this->password = $value;
                    break;
            }
        }
    }

    /**
     * Parse a connection string given in standard format (without dbtype://).
     *
     * @param  string $connection the connection string to be parsed
     * @throws ConnectionParserException the error preventing the parse
     * @throws \InvalidArgumentException the connection parameter is not a valid string
     */
    protected function parseStandardConnectionQuery($connection)
    {
    }

    public function getPDOConnection() : array
    {
        $query = $this->getPDODriverName() . ':';

        $query .= ((is_string($this->host)) && (strlen($this->host) > 0)) ?
            'host=' . $this->host . ';' : '';

        $query .= ((is_string($this->port)) && (strlen($this->port) > 0)) ?
            'port=' . $this->port . ';' : '';

        $query .= ((is_string($this->name)) && (strlen($this->name) > 0)) ?
            'dbname=' . $this->name . ';' : '';

        $dbUser = ((is_string($this->user)) && (strlen($this->user) > 0)) ? $this->user : null;
        $dbPass = ((is_string($this->password)) && (strlen($this->password) > 0)) ? $this->password : null;

        return [
            $query,
            $dbUser,
            $dbPass,
            $this->args
        ];
    }
}