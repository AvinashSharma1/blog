<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Core\Helpers\DbHelper;
use Illuminate\Support\Arr as IlluminateArr;
use DB;

class BaseModel extends Model
{
    const FROM_DATE     = 'from_date';
    const TO_DATE       = 'to_date';
    const SORT_BY       = 'sort_by';
    const SORT_ORDER    = 'sort_type';

    const IN            = 'IN';
    const NOT_IN        = 'NOT_IN';

    protected $params   = [];
    protected $query;

    /**
     * Sets the parameter array for use by the filter methods
     *
     * @param Model $query A query object to be used as basis for filters
     * @param array $params An array of parameters
     *
     * @return obj a new instance of this class
     */
    public function useValues($query, $params)
    {
    	$this->query = $query;
    	$this->params = $params;

    	return $this;
    }
    

    /**
     * Retrieves the query object used by the filter methods
     * This is used at the end to return a usable query 
     *
     * @return obj A query object
     */
    public function apply()
    {
        return $this->query;
    }

    /**
     * Evaluates the existence of a parameter value 
     * identified by the given key
     * 
     * @param string $key A parameter key
     *
     * @return boolean
     */
    public function hasParameter($key)
    {
        return (!empty($this->params) && isset($this->params[$key])
            && ($this->params[$key] === 0 || !empty($this->params[$key])))
            ? true
            :false;
    }

    /**
     * Applies a list of pre-defined filters
     * These filters are oriented towards param to table column matches
     *
     * @param array $filters An array of filters in the format parameter_key => [fieldname, operator, text_for_sprintf]
     *
     * @return object
     */ 
    public function useFilters($filters)
    {
        foreach($filters AS $key => $val)
        {
            if(!$this->hasParameter($key))
            {
                continue;
            }

            list($field, $operator, $value) = $val;

            $this->query = $this->query->where(
                $field, 
                $operator, 
                sprintf($value, $this->params[$key])
            );
        }

        return $this;
    }

    /**
     * A query filter helper function matches the status of a given $arrayToCompare to a given $stringToCompare
     * and in turn the resulting $statuses will then be fed to the whereIn or whereNotIn query
     *
     * @author Roy Reynes, Sameer Bele
     *
     * @param string $fieldToCompare
     * @param        $arrayToCompare
     * @param        $stringToCompare
     * @param string $operator
     * @return $this
     */
    public function compareField($fieldToCompare = 'is_active', $arrayToCompare, $stringToCompare, $operator = self::IN)
    {
        $arrayToCompare = array_change_key_case($arrayToCompare);

        $stringToCompare = strtolower($stringToCompare);

        $arrayToCompare = IlluminateArr::only($arrayToCompare, explode(',', $stringToCompare));

        if(empty($arrayToCompare))
        {
            return $this;
        }

        return $this->useList($fieldToCompare, $arrayToCompare, $operator);
    }

    /**
     * Add filters to a query using an array of arrays
     * where the parameter value is a key to an array of possible values.
     *
     * @param string $field A table field name
     * @param string $key A request parameter key
     * @param array $map A 2-dimensional associative array of numerically indexed arrays
     * @param string $operator Used to decide whether the comparison uses IN or NOT_IN
     *
     * @return obj
     */
    public function useMap($field, $key, $map, $operator)
    {
        if(!$this->hasParameter($key))
        {
            return $this;
        }

        $map = array_change_key_case($map);
        $index = strtolower($this->params[$key]);

        if(!in_array($index, array_keys($map)) || empty($map[$index]))
        {
            return $this;
        }

        return $this->useList($field, $map[$index], $operator);
    }

    /**
     * Add filters to a query using an array of values.
     * Useful for IN or NOT_IN query conditions.
     *
     * @param string $field A table field name
     * @param array $list A 1-dimensional array of values
     * @param string $operator Used to decide whether the comparison uses IN or NOT_IN
     *
     * @return obj
    */
    protected function useList($field, $list, $operator)
    {
        switch ($operator) {
           case self::IN:
               $this->query = $this->query->whereIn($field, $list);
               break;
           case self::NOT_IN:
               $this->query = $this->query->whereNotIn($field, $list);
               break;
        }
  
        return $this;
    }

    /**
     * Applies date filters found in the parameters
     *
     * @param array $filters An array of filters in the format parameter_key => [fieldname, operator, text_for_sprintf]
     *
     * @return obj The current instance of this class
     */
    public function useDateFilters($filters)
    {
        if(!$this->hasParameter(self::FROM_DATE) && !$this->hasParameter(self::TO_DATE))
        {
            return $this;
        }

        if(!isset($filters[self::FROM_DATE]) && !isset($filters[self::TO_DATE]))
        {
            return $this;
        }

        foreach($filters AS $key => $val)
        {
            $date = '';
            list($field, $operator, $value) = $val;
            switch ($key)
            {
                case self::FROM_DATE:
                    $date = DbHelper::validateDate($this->params[$key]) ? $this->params[$key] : DbHelper::getFromDate($this->params[$key]);
                    break;
                case self::TO_DATE:
                    $date = DbHelper::validateDate($this->params[$key]) ? $this->params[$key] : DbHelper::getToDate($this->params[$key]);
                    break;
            }

            $this->query = $this->query->where($field, $operator, $date);
        }

        return $this;

    }

    /**
     * Applies sorting using the given field that matches a parameter key found in filters
     * expected parameter names are
     * for sort_by: the param name that matches a table field name
     * for sort_type: the sort order ASC, or DESC, though sort_order might be more idiomatic
     *
     * @param array $filters An array of filters in the format parameter_key => [fieldname, operator, text_for_sprintf]
     * @return $this
     */
    public function useSortFilters($filters)
    {
        if(!$this->hasParameter(self::SORT_BY))
        {
            return $this;
        }

        foreach($filters AS $key => $val)
        {
            $sortOrder = $this->hasParameter(self::SORT_ORDER) ? $this->params[self::SORT_ORDER] : 'desc';

            list($field, $operator, $value) = $val;

            if($this->params[self::SORT_BY] != $key)
            {
                continue;
            }
            $this->query = $this->query->orderBy($field, $sortOrder);
        }


        return $this;
    }

    /**
     * A function caters DB::raw/callback as its $rawField
     *
     * @param string $whereMethod
     * @param string $rawField
     * @param string $operator
     * @param string $value
     * @return $this
     */
    public function useRawFilters($whereMethod = 'where', $rawField = '', $operator = '=', $value = '')
    {
        if(is_callable($rawField))
        {
            $this->query->{$whereMethod}($rawField);
        }
        else
        {
            $this->query->{$whereMethod}($rawField, $operator, $value);
        }

        return $this;
    }

    /**
     * Function that returns a callable closure - Useful when we have to apply group conditions in query
     * @param array $where
     * @param array $orWhere
     * @param $operator - operator to pass [=, like, !=]
     * @param $operator2 - operator to pass [=, like, !=] default null
     * @return \Closure
     */
    public function createCallableFilter($where, $orWhere, $operator = '=', $operator2 = null)
    {
        list($fromField, $fromValue) = each($where);
        list($orFromField, $orFromValue) = each($orWhere);

        $operator2 = ($operator2)? $operator2 : $operator;

        return function($query) use ($fromField, $fromValue, $orFromField, $orFromValue, $operator, $operator2) {
            $query->where(DB::raw('LOWER('.$fromField.')'), $operator, strtolower($fromValue))
                ->orWhere(DB::raw('LOWER('.$orFromField.')'), $operator2, strtolower($orFromValue));
            };
    }

}