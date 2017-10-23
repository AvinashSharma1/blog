<?php 

namespace App\Modules\Core\Helpers;

use DB;
use DateTime;


class DbHelper
{
    const OPERATION_WRITE = 'write';

    /**
     * This function returns the column count of the given object query
     *
     */
    public static function getColumnCount($query, $fieldToBeCounted = '')
    {
		$field = !empty($fieldToBeCounted) ? $fieldToBeCounted : '*';
		return $query->select(DB::raw('count('.$field.') as count'))->get();
    }

    /**
     * this function used to check whether date format is Y-m-d h:i:s or not
     * @param $date date  is that needs to be check with this value
     * @return true or false
     *
     */
    public static function validateDate($date)
    {
        $d = DateTime::createFromFormat('Y-m-d H:i:s', $date);
        return $d && $d->format('Y-m-d H:i:s') === $date;
    }

    /**
     * this function append date with time
     * @param $from_date is that needs to be append with time value
     * @return date format is Y-m-d H:i:s
     *
     */
    public static function getFromDate($from_date){
        return date('Y-m-d H:i:s',strtotime($from_date.' 00:00:00'));
    }

    /**
     * this function append date with time
     * @param $to_date is that needs to be append with time value
     * @return date format is Y-m-d H:i:s
     *
     */
    public static function getToDate($to_date){
        return date('Y-m-d H:i:s',strtotime($to_date.' 23:59:59'));
    }

    /**
     * Retrieves the database name identified by a connection identifier
     *
     * @param string $connection
     * @param string $operation
     * @return string The database name for the connection identifier found in config
     */
    public static function getDbName($connection, $operation = self::OPERATION_WRITE)
    {
        $db = ( (boolean) REPLICATION_ENABLE )
                ? config('database.connections.' . $connection . '.' . $operation . '.database')
                : config('database.connections.' . $connection . '.database');

        return !empty($db) ? $db . '.' : '';
    }

}