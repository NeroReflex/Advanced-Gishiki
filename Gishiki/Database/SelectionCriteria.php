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
 * This class is used to represent a selection criteria for database rows
 *
 * @author Benato Denis <benato.denis96@gmail.com>
 */
final class SelectionCriteria
{
    const AND_Historic_Marker = 0b10000000;
    
    /**
     * @var array keeps track of the order clauses were inserted
     */
    protected $historic = [];
    
    /**
     * @var array both 'or' and 'and' are two arrays of sub-arrays
     */
    protected $criteria = [
        'and' => [],
        'or'  => []
    ];
    
    /**
     * Create a sub-clause and append it to the where clause using an and as conjunction
     *
     * @param  string  $field        the name of the field/column to be related with the data
     * @param  integer $relationship the relationship between the field and the data
     * @param  mixed   $data         the data to be related with the field
     * @return \Gishiki\Database\SelectionCriteria the updated selection criteria
     * @throws \InvalidArgumentException one parameter has a wrong type
     */
    public function and_where($field, $relationship, $data)
    {
        if (!is_string($field) || (strlen($field) <= 0)) {
            throw new \InvalidArgumentException('the field name must be a string');
        }
        if (($relationship != FiledRelationship::EQUAL) &&
                ($relationship != FiledRelationship::NOT_EQUAL) &&
                ($relationship != FiledRelationship::LESS_THAN) &&
                ($relationship != FiledRelationship::LESS_OR_EQUAL_THAN) &&
                ($relationship != FiledRelationship::GREATER_THAN) &&
                ($relationship != FiledRelationship::GREATER_OR_EQUAL_THAN) &&
                ($relationship != FiledRelationship::IN_RANGE) &&
                ($relationship != FiledRelationship::NOT_IN_RANGE) &&
                ($relationship != FiledRelationship::LIKE) &&
                ($relationship != FiledRelationship::NOT_LIKE)) {
            throw new \InvalidArgumentException('the relationship between a column and its value must be expressed by one of FiledRelationship constants');
        }
        if (is_object($data)) {
            throw new \InvalidArgumentException('the field data cannot be a php object');
        }
        
        $this->criteria['and'][] = [
            0 => $field,
            1 => $relationship,
            2 => $data
        ];
        
        $this->historic[] = self::AND_Historic_Marker | (count($this->criteria['and']) - 1);
        
        //return the modified filter
        return $this;
        //this is really important as it
        //allows the developer to chain
        //filter modifier functions
    }
    
    /**
     * Create a sub-clause and append it to the where clause using an or as conjunction
     *
     * @param  string  $field        the name of the field/column to be related with the data
     * @param  integer $relationship the relationship between the field and the data
     * @param  mixed   $data         the data to be related with the field
     * @return \Gishiki\Database\SelectionCriteria the updated selection criteria
     * @throws \InvalidArgumentException one parameter has a wrong type
     */
    public function or_where($field, $relationship, $data)
    {
        if (!is_string($field)) {
            throw new \InvalidArgumentException('the field name must be a string');
        }
        if (($relationship != FiledRelationship::EQUAL) &&
                ($relationship != FiledRelationship::NOT_EQUAL) &&
                ($relationship != FiledRelationship::LESS_THAN) &&
                ($relationship != FiledRelationship::LESS_OR_EQUAL_THAN) &&
                ($relationship != FiledRelationship::GREATER_THAN) &&
                ($relationship != FiledRelationship::GREATER_OR_EQUAL_THAN) &&
                ($relationship != FiledRelationship::IN_RANGE) &&
                ($relationship != FiledRelationship::NOT_IN_RANGE) &&
                ($relationship != FiledRelationship::LIKE) &&
                ($relationship != FiledRelationship::NOT_LIKE)) {
            throw new \InvalidArgumentException('the relationship between a column and its value must be expressed by one of FiledRelationship constants');
        }
        if (is_object($data)) {
            throw new \InvalidArgumentException('the field data cannot be a php object');
        }
        
        $this->criteria['or'][] = [
            0 => $field,
            1 => $relationship,
            2 => $data
        ];
        
        $this->historic[] = count($this->criteria['or']) - 1;
        
        //return the modified filter
        return $this;
        //this is really important as it
        //allows the developer to chain
        //filter modifier functions
    }
    
    private function export()
    {
        $export = [
            'historic' => $this->historic,
            'criteria' => $this->criteria
        ];
        
        return $export;
    }
}
