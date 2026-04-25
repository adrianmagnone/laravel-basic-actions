<?php

if (! function_exists('today_formated'))
{
    function today_formated()
    {
        $new_date = new DateTime();
        $format = config('lba.sql_date_formats.from');
        return $new_date->format($format);;
    }
}

if (! function_exists('sql_date_formated'))
{
    function sql_date_formated($date)
    {
        $format = config('lba.sql_date_formats');
        $new_date = DateTime::createFromFormat($format['from'], $date);

        return $new_date->format($format['to']);
    }
}