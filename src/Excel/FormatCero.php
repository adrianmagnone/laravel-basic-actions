<?php

namespace Aiglos\Lba\Excel;

use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class FormatCero implements IFormat
{
    public static function headerStyle() : Array
    {
        return [];
    }

	public static function h1Style() : Array
    {
        return [];
    }

	public static function h2Style() : Array
    {
        return [];
    }

	public static function bodyStyle() : Array
    {
        return [];
    }

	public static function footerStyle() : Array
    {
        return [];
    }

	public static function headerGroupStyle() : Array
    {
        return [];
    }

    public static function tableFormats() : Array
    {
        return [];
    }

	public static function cellFormats(String $tipo) : Array
    {
        $formats = [
            'money' => [
                'numberFormat' => [
                    'formatCode' => NumberFormat::FORMAT_CURRENCY_USD
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
}