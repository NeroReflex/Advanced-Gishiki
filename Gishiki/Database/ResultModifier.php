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

namespace Gishiki\Database;

/**
 * An helper class used to modify the result set of a database operation.
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
final class ResultModifier
{
    
    /**
     * @var array filters to be applied to the result set
     */
    protected $resultChanger;
    
    
    public static function Initialize($init = null)
    {
        if ((!is_null($init)) && (!is_array($init))) {
            throw new \InvalidArgumentException('The initialization filter con only be null or a valid array');
        }
          
        //create a new result modifier
        $modifier = new self();
        
        if (is_array($init)) {
            foreach ($init as $key => $value) {
                if (is_string($key)) {
                    if (strcmp($key, "skip") == 0) {
                        $modifier->skip($value);
                        continue;
                    }

                    if (strcmp($key, "limit") == 0) {
                        $modifier->limit($value);
                        continue;
                    }

                    $modifier->order($key, $value);
                }
            }
        }
        
        // return the new result modifier
        return $modifier;
    }
    
    /**
     * Create a new result modifier that acts as "no filters".
     */
    public function __construct()
    {
        //no limit by default
        $this->resultChanger = [
            'sort' => [
                '_id' => FieldOrdering::ASC
            ],
            'limit' => -1,
            'skip' => 0
        ];
    }
    
    /**
     * Change the order of elements in the result set.
     *
     * @param string $field the name of the field to be used for ordering
     * @param int $order the order to be applied (one of FieldOrdering consts)
     * @return \Gishiki\Database\ResultModifier the modified filter
     * @throws \InvalidArgumentException passed input is not valid or incompatible type
     */
    public function order($field, $order)
    {
        //check for the type of the input
        if (!is_string($field)) {
            throw new \InvalidArgumentException("The name of the field to be ordered must be given as a string");
        }
        if (($order !== FieldOrdering::ASC) && ($order !== FieldOrdering::DESC)) {
            throw new \InvalidArgumentException("The ordering mus be given as ASC or DESC (see FieldOrdering)");
        }
        
        //set ordering
        $this->resultChanger['sort'][$field] = $order;
        
        //return the modified filter
        return $this;
        //this is really important as it
        //allows the developer to chain
        //filter modifier functions
    }
    
    /**
     * Change the limit of the elements in the result set.
     *
     * @param int $limit the maximum number of results that can be fetched from the database
     * @return \Gishiki\Database\ResultModifier the modified filter
     * @throws \InvalidArgumentException passed input is not valid or incompatible type
     */
    public function limit($limit = -1)
    {
        //check for the type of the input
        if (!is_int($limit)) {
            throw new \InvalidArgumentException("The limit must be given as an integer number");
        }
        
        //change the limit
        $this->resultChanger['limit'] = $limit;
        
        //return the modified filter
        return $this;
        //this is really important as it
        //allows the developer to chain
        //filter modifier functions
    }
    
    /**
     * Change the offset of elements in the result set.
     *
     * @param int $offset the offset to be applied
     * @return \Gishiki\Database\ResultModifier the modified filter
     * @throws \InvalidArgumentException passed input is not valid or incompatible type
     */
    public function skip($offset = -1)
    {
        //check for the type of the input
        if (!is_int($offset)) {
            throw new \InvalidArgumentException("The offset must be given as an integer number");
        }
        
        //change the limit
        $this->resultChanger['skip'] = $offset;
        
        //return the modified filter
        return $this;
        //this is really important as it
        //allows the developer to chain
        //filter modifier functions
    }
    
    protected function export()
    {
        $export = $this->resultChanger;
        
        if ($export['limit'] <= 0) {
            unset($export['limit']);
        }
        if ($export['skip'] < 0) {
            unset($export['skip']);
        }
        
        return $export;
    }
}
