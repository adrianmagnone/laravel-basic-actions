<?php

namespace Aiglos\Lba\Excel;

use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class FormatBase implements IFormat
{
    public function headerStyle() : Array
    {
        return [
            'font' => [
                'bold'  => true,
                'size'  => 10,
                'color' => [ 'rgb' => 'FFFFFF' ],
            ],
            'borders' => [
                'bottom' => [
                    'style' => Border::BORDER_THIN
                ]
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => [ 'argb' => 'FF00215A' ],
                'endColor' => [ 'argb' => 'FF00215A' ],
            ]
        ];
    }

	public function h1Style() : Array
    {
        return [
            'font' => [
                'bold'  => true,
                'size'  => 12,
                'color' => [ 'rgb' => '00215A' ],
            ],
        ];
    }

	public function h2Style() : Array
    {
        return [
            'font' => [
                'color' => [ 'rgb' => '127622' ],
            ],
        ];
    }

	public function bodyStyle() : Array
    {
        return [
            'font' => [
                'size'  => 10,
            ],
        ];
    }

	public function footerStyle() : Array
    {
        return [
            'font' => [
                'bold' => true,
            ],
        ];
    }

	public function headerGroupStyle() : Array
    {
        return [
            'font' => [
                'bold'  => true,
                'size'  => 10,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => [ 'argb' => 'FFCCCCCC' ],
                'endColor' => [ 'argb' => 'FFCCCCCC' ],
            ]
        ];
    }

	public function cellFormats(String $tipo) : Array
    {
        $formats = [
            'money' => [
                'numberFormat' => [
                    'formatCode' => NumberFormat::FORMAT_CURRENCY_USD_SIMPLE
                ]
            ],
            'decimal' => [
                 'numberFormat' => [
                    'formatCode' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED2
                ]
            ],
            'percent' => [
                'numberFormat' => [ 
                    'formatCode' => NumberFormat::FORMAT_PERCENTAGE_00  
                ]
            ],
            'date' => [
                'numberFormat' => [ 
                    'formatCode' => NumberFormat::FORMAT_DATE_DDMMYYYY  
                ]
            ]
        ];

        if (array_key_exists($tipo, $formats))
            return $formats($tipo);

        return [];
    }
}