<?php

namespace Aiglos\Lba\Tests\Unit;

use Aiglos\Lba\Export\CreateCsvBase;
use Aiglos\Lba\Tests\TestCase;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;

class CreateCsvBaseTest extends TestCase
{
    public function test_it_accepts_valid_csv_parameters(): void
    {
        $this->expectNotToPerformAssertions();

        $csv = new CreateCsvBase();

        $csv->setSeparator(';');
        $csv->setEnclosure("'");
        $csv->setEscape('');
        $csv->setEol("\n");
    }

    public function test_it_rejects_a_separator_that_is_not_one_byte(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new CreateCsvBase())->setSeparator('||');
    }

    public function test_it_rejects_an_enclosure_that_is_not_one_byte(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new CreateCsvBase())->setEnclosure('""');
    }

    public function test_it_rejects_an_enclosure_that_matches_the_separator(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new CreateCsvBase())->setEnclosure(',');
    }

    public function test_it_rejects_an_escape_that_is_longer_than_one_byte(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new CreateCsvBase())->setEscape('\\\\');
    }

    public function test_it_rejects_an_empty_line_ending(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new CreateCsvBase())->setEol('');
    }

    public function test_it_neutralizes_formula_like_values_in_headers_and_rows(): void
    {
        Storage::fake('local');

        $csv = new CreateCsvBase();
        $csv->setFileName('formula-safety.csv');
        $csv->setHeaders(['=Header']);
        $csv->setData(collect(['source']));
        $csv->run(fn () => [[
            '=1+1',
            '  +SUM(A1:A2)',
            '-1+1',
            '@SUM(A1:A2)',
            "\t=1+1",
            'ordinary text',
            -12,
        ]]);

        $csv->output();

        $contents = Storage::disk('local')->get('formula-safety.csv');
        $lines = explode("\r\n", rtrim($contents, "\r\n"));
        $headers = str_getcsv($lines[0], ',', '"', '\\');
        $row = str_getcsv($lines[1], ',', '"', '\\');

        $this->assertSame(["'=Header"], $headers);
        $this->assertSame([
            "'=1+1",
            "'  +SUM(A1:A2)",
            "'-1+1",
            "'@SUM(A1:A2)",
            "'\t=1+1",
            'ordinary text',
            '-12',
        ], $row);
    }
}
