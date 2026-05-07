<?php

namespace Aiglos\Lba\Tests\Unit;

use Aiglos\Lba\Tests\TestCase;

class HelpersTest extends TestCase
{
    public function test_today_formated_returns_current_date_in_config_format()
    {
        // Assuming config('lba.sql_date_formats.from') is 'd/m/Y' by default
        $expected = date('d/m/Y');
        $result = today_formated();
        $this->assertEquals($expected, $result);
    }

    public function test_date_from_format_to_converts_date_correctly()
    {
        $date = '2023-04-27';
        $from = 'Y-m-d';
        $to = 'd/m/Y';
        $result = date_from_format_to($date, $from, $to);
        $this->assertEquals('27/04/2023', $result);
    }

    public function test_date_from_format_to_handles_different_formats()
    {
        $date = '04/27/2023';
        $from = 'm/d/Y';
        $to = 'Y-m-d';
        $result = date_from_format_to($date, $from, $to);
        $this->assertEquals('2023-04-27', $result);
    }

    public function test_date_from_format_to_name_handles_periodo()
    {
        $date = '04/27/2023';
        $from = 'm/d/Y';
        $to = 'MMMM YYYY';
        $result = date_from_format_to_name($date, $from, $to);
        $this->assertEquals('abril 2023', $result);
    }
}