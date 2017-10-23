<?php 
namespace App\Helpers;

class Html
{

    /**
     * Encode String Conert HTML Chars
     *
     * @param string $str
     * @param string $allowed_tags
     * @return string
     */
    public static function purify($str, $allowed_tags = null)
    {
        $value = htmlentities($str, ENT_QUOTES | ENT_IGNORE, "UTF-8");

        if(empty($allowed_tags)) return $value;

        return preg_replace('#&lt;(/?(?:'.$allowed_tags.')( ?)?/?)&gt;#', '<\1>', $value);
    }

    /**
     * Cleaning the string for xss attack
     *
     * @param mix $dirty
     * @param null $allowed_tags
     * @return string
     */
    public static function clean($dirty, $allowed_tags = null)
    {
        if(is_array($dirty)) 
        {
            return array_map(function ($item) use ($allowed_tags) {
                return self::clean($item, $allowed_tags);
            }, $dirty);
        }

        return self::purify($dirty, $allowed_tags);
    }

}