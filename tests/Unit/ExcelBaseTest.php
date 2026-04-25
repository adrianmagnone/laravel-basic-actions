<?php

namespace Aiglos\Lba\Tests\Unit;

use Aiglos\Lba\Tests\TestCase;
use Aiglos\Lba\Excel\ExcelBase;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class ExcelBaseTest extends TestCase
{
    /**
     * Test that toDate converts a valid date string to an Excel serial number
     */
    public function test_toDate_converts_valid_date_string_to_excel_serial()
    {
        $dateString = '2026-04-25';
        $result = ExcelBase::toDate($dateString);
        
        // Verify the result is a numeric value (Excel serial number)
        $this->assertIsNumeric($result);
        $this->assertIsFloat($result);
        $this->assertGreaterThan(0, $result);
    }

    /**
     * Test that toDate returns the same value as PhpSpreadsheet's PHPToExcel for a known date
     */
    public function test_toDate_returns_correct_excel_serial_for_specific_date()
    {
        $dateString = '2026-04-25';
        $excelDate = ExcelBase::toDate($dateString);
        
        // Create a DateTime object and verify it matches what PHPToExcel would return
        $dateObj = new \DateTime($dateString);
        $timestamp = $dateObj->getTimestamp();
        $expectedExcelDate = Date::PHPToExcel($timestamp);
        
        // The values should be approximately equal (within 1 day due to timezone differences)
        $this->assertEqualsWithDelta($excelDate, $expectedExcelDate, 1);
    }

    /**
     * Test that toDate handles different date formats
     */
    public function test_toDate_handles_different_date_formats()
    {
        $dates = [
            '2026-04-25',
            'April 25, 2026',
            '2026/04/25',
            '25 April 2026',
        ];
        
        foreach ($dates as $date) {
            $result = ExcelBase::toDate($date);
            $this->assertIsNumeric($result);
            $this->assertGreaterThan(0, $result);
        }
    }

    /**
     * Test that toDate handles dates with time information
     */
    public function test_toDate_handles_dates_with_time()
    {
        $dateString = '2026-04-25 14:30:45';
        $result = ExcelBase::toDate($dateString);
        
        $this->assertIsNumeric($result);
        $this->assertGreaterThan(0, $result);
    }

    /**
     * Test that toDate handles different years
     */
    public function test_toDate_handles_different_years()
    {
        $dates = [
            '2000-01-01',
            '2020-06-15',
            '2026-12-31',
        ];
        
        foreach ($dates as $date) {
            $result = ExcelBase::toDate($date);
            $this->assertIsNumeric($result);
            $this->assertGreaterThan(0, $result);
        }
    }

    /**
     * Test that toDate is a static method and can be called on the class
     */
    public function test_toDate_is_static_method()
    {
        $result = ExcelBase::toDate('2026-04-25');
        $this->assertIsNumeric($result);
    }
}
