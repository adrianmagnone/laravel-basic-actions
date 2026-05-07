<?php

namespace Aiglos\Lba\Excel;

use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Table\TableStyle;

class FormatBaseTable implements IFormat
{
    public static function headerStyle() : Array
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

	public static function h1Style() : Array
    {
        return [
            'font' => [
                'bold'  => true,
                'size'  => 12,
                'color' => [ 'rgb' => '00215A' ],
            ],
        ];
    }

	public static function h2Style() : Array
    {
        return [
            'font' => [
                'color' => [ 'rgb' => '127622' ],
            ],
        ];
    }

	public static function bodyStyle() : Array
    {
        return [
            'font' => [
                'size'  => 10,
            ],
        ];
    }

	public static function footerStyle() : Array
    {
        return [
            'font' => [
                'bold' => true,
            ],
        ];
    }

	public static function headerGroupStyle() : Array
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

	public static function cellFormats(String $tipo) : Array
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
            return $formats[$tipo];

        return [];
    }

    public static function tableFormats() : Array
    {
        return [
            'theme'             => TableStyle::TABLE_STYLE_MEDIUM2,
            'showFirstColumn'   => false,
            'showLastColumn'    => false,
            'showRowStripes'    => true,
            'showColumnStripes' => false,
        ];
    }
}