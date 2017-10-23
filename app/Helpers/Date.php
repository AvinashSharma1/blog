<?php

namespace App\Modules\Core\Helpers;

use DateTime;

class Date
{
    /**
     * Get the number of days' difference
     *
     * @param date        $start_date
     * @param date|null   $end_date
     * @param string|null $date_modification    Any DateTime relative format
     * @param string      $format               Any DateInterval format
     * @return string
     */
    public static function getDateDifference($start_date, $end_date = null, $date_modification = null, $format = '%a')
    {
        $start_date_str_to_time = strtotime($start_date);
        $start_date = new DateTime(date('Y-m-d', $start_date_str_to_time));

        // Get the days difference from Start_date to +/- days/months
        if(!empty($date_modification) && empty($end_date))
        {
            return (new DateTime(date('Y-m-d', strtotime($date_modification, $start_date_str_to_time))))
                ->diff($start_date)->format($format);
        }
        // Get the days difference from Start_date to End_date
        else if(empty($date_modification) && !empty($end_date))
        {
            return (new DateTime(date('Y-m-d', strtotime($end_date))))
                ->diff($start_date)->format($format);
        }

        // Get the days difference from Start_date to +/- days/months of End_date
        return (new DateTime(date('Y-m-d', strtotime($date_modification, strtotime($end_date)))))
            ->diff($start_date)->format($format);
    }

    /**
     * Get date as per current timezone from given date and format
     *
     * @param $date string date
     * @param $format string format to be displayed
     * @return string
     */
    public static function to_timezone($date_time, $format = 'M j, Y g:i a')
    {
        $date = new DateTime($date_time);

        // Change current timezone
        if (defined('APPLICATION_TIMEZONE'))
        {
            $date->setTimezone(new DateTimeZone(APPLICATION_TIMEZONE));
        }

        return $date->format($format);
    }
}