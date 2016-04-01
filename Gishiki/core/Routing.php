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

namespace Gishiki\Core {

    /**
     * The Gishiki Routing provider
     *
     * @author Benato Denis <benato.denis96@gmail.com>
     */
    abstract class Routing {
        //methods collection
        const GET       = 0;
        const POST      = 1;
        const DELETE    = 2;
        const HEAD      = 3;
        const PUT       = 4;
        
        //events collections
        const NotFoud   = 5;
        
        //functions called on particular events
        private static $notFound = null;
        
        //was a routing function being executed?
        private static $executed = FALSE;
        
        /**
         * Automatically called by the framework: perform a reset 
         * to the router of the framework 
         */
        public static function Initialize() {
            //setup stubs on routing events
            static::$notFound = function () {    die("404 Not Found");  };
        }
        
        /**
         * Automatically called by the framework:
         * end the execution of the router 
         */
        public static function Deinitialize() {
            if (!static::$executed)
            {   call_user_func(static::$notFound);  }
        } 
        
        /**
         * Get the URI requested by the client.
         * 
         * @return string the request from the client
         */
        public static function getRequestURI() {
            $basepath = implode('/', array_slice(explode('/', filter_input(INPUT_SERVER, 'SCRIPT_NAME')), 0, -1)) . '/';
            $uri = substr(filter_input(INPUT_SERVER, 'REQUEST_URI'), strlen($basepath));
            if (strstr($uri, '?'))
            {   $uri = substr($uri, 0, strpos($uri, '?'));  }
            $uri = '/' . trim($uri, '/');
            return urldecode($uri);
        }
        
        /**
         * Return the method used by the client to reach the framework execution
         * 
         * @return integer one of GET, POST, HEAD, DELTE or put contants
         */
        public static function getRequestMethod() {
            //get the string representation of the used method
            switch (strtoupper(filter_input(INPUT_SERVER, 'REQUEST_METHOD'))) {
                case "POST":
                    return static::POST;
                case "HEAD":
                    return static::HEAD;
                case "PUT":
                    return static::PUT;    
                case "DELETE":
                    return static::DELETE;
                default:
                    return static::GET;
            }
        }
        
        /**
         * Set the routing for a given method/URI pair (or group of URI if regex is used).
         * An example of usage can be:
         * <code>
         * use Gishiki\Core;
         * 
         * Core\Routing::setRoute(Core\Routing::GET, "/user/{id}", function ($params) {
         *      foreach ($params as $param_name => $param_value) {
         *          echo $param_name." => ".$param_value;
         *      }
         * });
         * 
         * //if the framework is installed at https://site.com/ when an user requests
         * //https://site.com/user/5476 the user will see: id => 5476
         * </code>
         * 
         * Be aware that the passed parameter is an object instance from 
         * \Gishiki\Algorithms\CyclableCollection if regex routing is used, 
         * otherwise it is NULL
         * 
         * @param integer $Method one request method chosen from GET, POST, HEAD ecc.... 
         * @param string $URI the URI that will bring to the function execution
         * @param function $function the function executed when the URL is called
         */
        public static function setRoute($Method, $URI, $function) {
            $real_URI = static::getRequestURI();
            
            if ((!static::$executed) && (static::getRequestMethod() == $Method)) {
                //this will contain the matched expressions placeholders
                $params = array();

                //detect if regex are involved in the furnished URI
                if (preg_match_all('/\\{[[:alnum:]]+\\}/', $URI, $params)) {
                    //stupid preg_match_all stop breaking my algorithm!
                    $params = $params[0];

                    //this is the regular expression an URL must match to trigger the function execution
                    $RegexURI = $URI;
                    
                    //sobstitute each virtual param with a regex that matches everything
                    foreach ($params as &$param_value) {
                        $RegexURI = str_replace($param_value, "([^/]*)", $RegexURI);
                    }
                    
                    //test the regex match of the current URI with the real URI
                    if (preg_match("@^".$RegexURI."*$@i", $real_URI)) {
                        //setup the result of the routing
                        $resolved_regex = new \Gishiki\Algorithms\CyclableCollection();
                        
                        //get matches (complex algorithm here!)
                        $to_be_splitted = $URI;
                        foreach ($params as &$param) {
                            //from /book/{id}/page/{pg} I extract /book/ and, next cycle /page/
                            $before_after = explode($param, $to_be_splitted, 2);
                            $to_be_splitted = str_replace($before_after[0], "", $to_be_splitted);
                            $to_be_splitted = str_replace($param, "", $to_be_splitted);
                            
                            //than I remove /book/ from my real uri, and i look for what it is between the start of the string and the near / or end of string
                            $real_URI = str_replace($before_after[0], "", $real_URI);
                            for ($i = 0; ($i < strlen($real_URI) && ($real_URI[$i] != '/')); $i++) ;
                            
                            //what i have found is the real value of the param
                            $param_real_value = substr($real_URI, 0, $i);
                            
                            //i just remove it to avoid breaking the alogirth for the next execution of the cycle
                            $real_URI = str_replace($param_real_value, "", $real_URI);
                            
                            //I am doing all this just for this line of code:
                            $resolved_regex->set(substr($param, 1, strlen($param) - 2), $param_real_value);
                        }
                        
                        //finally trigger the function execution
                        $function($resolved_regex);
                        static::$executed = TRUE;
                    }
                    
                } else if ($real_URI == $URI) { //no regex routing: just check if the current request is done to the given routing
                    //trigger the function execution
                    $function(null);
                    static::$executed = TRUE;
                }
            }
        }
        
        /**
         * Set the function callback for the given error type.
         * 
         * Errors are methods-free, but you can query the used 
         * method inside the function, if you really need to...
         * 
         * @param integer $error one of the error identifiers
         * @param function $function this is the function automatically called when that error type occurs
         */
        public static function setErrorCallback($error, $function) {
            if ($error == static::NotFoud) {
                static::$notFound = $function;
            }
        }
    }
}