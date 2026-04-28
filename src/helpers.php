<?php

if (! function_exists('today_formated'))
{
    function today_formated($format = null)
    {
        $new_date = new DateTime();
        $format = $format ?? config('lba.sql_date_formats.from');
        return $new_date->format($format);;
    }
}

if (! function_exists('sql_date_formated'))
{
    function sql_date_formated($date)
    {
        $format = config('lba.sql_date_formats');

        return date_from_format_to($date, $format['from'], $format['to']);
    }
}

if (! function_exists('date_from_format_to'))
{
    function date_from_format_to($date, $from, $to)
    {
        try
		{
            return \Carbon\Carbon::createFromFormat($from, $date)
                ->locale('es')
                ->isoFormat($to);
		}
		catch (\Throwable $th)
		{
			return '';
		}
    }
}