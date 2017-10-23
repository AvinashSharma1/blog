<?php 
namespace App\Helpers;

use Illuminate\Support\Arr as IlluminateArr;
use Mews\Purifier\Facades\Purifier;
use App\Helpers\Html;

class Arr
{
    /**
     * Get data from an array by providing its key with trapping on null arrays
     * This is to shorten all trappings in codes
     * @param  array $arr
     * @param  string $key
     * @param  string $default
     * @return mixed
     */
    public static function getArrayValue($arr, $key, $default = NULL)
    {
        return isset($arr[$key]) ? $arr[$key] : $default;
    }

    /**
     * Get The prurified data array
     * @param type $data
     * @return array
     */
    public static function getPurifiedDataArray($data = [], $html_sanitiz = true)
    {
        foreach($data as $key=>$r)
        {
            if(is_object($data[$key])|| is_array($data[$key]))
            {
                //Clean HTML
                array_walk_recursive(
                    $data[$key], 
                    function(&$v, $k) use ($html_sanitiz)
                    {
                        $v = is_object($v) ? (array) $v : $v;
                        $v = ($html_sanitiz) ? Html::clean($v) : Purifier::clean($v);
                    }
                );
            }
            else 
            {
                $data[$key] = ($html_sanitiz) ? Html::clean($data[$key]) : Purifier::clean($data[$key]);
            }
        }

        return $data;
    }

    /**
     * A private function that checks whether a keys/key exist in a given $arr
     *
     * @param $keys
     * @param $array
     * @return bool
     */
    public static function keysExist($keys, $array)
    {
        $exists = true;

        foreach ($keys as $key)
        {
            $result = IlluminateArr::exists($array, $key);

            if(false === $result)
            {
                $exists = false;
                break;
            }
        }

        return $exists;
    }

    /**
     * Flatten associative array
     *
     * @param        $array
     * @param string $prefix
     * @return array
     */
    public static function flatten($array, $prefix = '')
    {
        $result = array();

        foreach ($array as $key => $value)
        {
            $new_key = $prefix . (empty($prefix) ? '' : '.') . $key;

            if (is_array($value))
            {
                $result = array_merge($result, self::flatten($value, $new_key));
            }
            else
            {
                $result[$new_key] = $value;
            }
        }

        return $result;
    }

    /**
     * Flatten multi-dimensional associative array
     *
     * @param        $array
     * @param string $prefix
     * @return array
     */
    public static function multiFlatten($array, $prefix = '')
    {
        $array = array_map(function($value) use ($prefix){
            return self::flatten($value, $prefix);
        }, $array);

        return $array;
    }
}