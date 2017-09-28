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

namespace Gishiki\Algorithms\Strings;

/**
 * An helper class for string manipulation.
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
abstract class Manipulation
{
    /**
     * Convenient function that behave exactly like str_replace for the first occurrence only.
     *
     * @param string $pattern     the pattern to be replaced
     * @param string $replacement the string to replace the first matched pattern
     * @param string $string      the string to search the pattern into
     *
     * @return string the new string with the first matched pattern replaced
     */
    public static function replaceOnce($pattern, $replacement, $string)
    {
        if (strpos($string, $pattern) !== false) {
            $occurrence = strpos($string, $pattern);

            return substr_replace($string, $replacement, $occurrence, strlen($pattern));
        }

        return $string;
    }

    /**
     * Convenient function that behave exactly like str_replace for the first occurrence only.
     *
     * @param array  $patterns    the list of pattern to be replaced
     * @param string $replacement the string to replace the first matched pattern
     * @param string $string      the string to search the pattern into
     *
     * @return string the new string with the first matched pattern replaced
     */
    public static function replaceList($patterns, $replacement, $string)
    {
        foreach ($patterns as $pattern) {
            $string = str_replace($pattern, $replacement, $string);
        }

        return $string;
    }

    /**
     * Get the string between two substrings.
     *
     * @param string $string the string to be analyzed
     * @param string $start  the first substring
     * @param string $end    the second substring
     *
     * @return string|bool the string between the two substrings, or FALSE
     */
    public static function getBetween($string, $start, $end)
    {
        $string = ' '.$string;
        $ini = strpos($string, $start);
        $ini += strlen($start);
        $eni = strpos($string, $end, $ini);
        $len = $eni - $ini;

        return (($eni !== false) && ($ini !== false)) ?
            substr($string, $ini, $len) : false;
    }

    /**
     * Interpolate a PHP string:
     * perform a substitution of {{name}} with the value of the $params['name'].
     *
     * Note: $params['name'] can be an object that implements __toString()
     *
     * @param string $string
     * @param array  $params
     *
     * @return string the interpolated string
     */
    public static function interpolate($string, array $params)
    {
        //perform the interpolation
        foreach (array_keys($params) as $interpolation) {
            $currentInterpolation = (string) $interpolation;
            $string = str_replace('{{'.$currentInterpolation.'}}', (string) $params[$interpolation], $string);
        }

        //return the interpolated string
        return $string;
    }
}
