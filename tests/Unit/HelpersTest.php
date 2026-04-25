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
}