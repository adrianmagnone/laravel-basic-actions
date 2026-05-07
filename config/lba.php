<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Exportacion de Datos
    |--------------------------------------------------------------------------
    | 
    | memoria_excel:
    | Se puede definir la cantidad de memoria que se usara para la exportacion.
    | Posibles valores: se pueden usar los valores aceptados por memory_limit 
    | por ejemplo: '1024M', '2048M', '4096M', '8192M'.
    | 
    | cookie_excel:
    | Se puede definir los valores que se usara para establecer la cookie de descarga.
    |
    | clase_formato:
    | Se puede definir la clase que se usara para formatear los datos antes de
    | exportarlos a Excel. Esta clase debe implementar la interfaz \Aiglos\Lba\Excel\IFormat.
    |
    | excelTable:
    | Se puede definir si se usara el formato de tabla de Excel para las datos exportados. Posibles valores: 0 o 1  
    |  
    */
    
    'exportar' => [
        'memoria_excel' => env('DEFINE_MEM_EXCEL', '2048M'),
        'cookie_excel' => [
            'name'     => env('DEFINE_EXCEL_COOKIE_NAME', 'Pagos[downloadXlsToken]'),
            'value'    => env('DEFINE_EXCEL_COOKIE_VALUE', null),
            'domain'   => env('DEFINE_EXCEL_COOKIE_DOMAIN', 'localhost'),
            'path'     => env('DEFINE_EXCEL_COOKIE_PATH', '/pagos'),
            'expire'   => env('DEFINE_EXCEL_COOKIE_EXPIRE', 60*60),
            'secure'   => env('DEFINE_EXCEL_COOKIE_SECURE', false),
            'httpOnly' => env('DEFINE_EXCEL_COOKIE_HTTPONLY', false)
        ],
        'clase_formato' => env('DEFINE_CLASE_FORMATO_EXCEL', Aiglos\Lba\Excel\FormatBase::class),
        'excelTable'    => env('DEFINE_EXCEL_TABLE', 1)
    ],



    /*
    |--------------------------------------------------------------------------
    | Registro de Consultas SQL
    |--------------------------------------------------------------------------
    |
    | Con este valor se puede activar el registro de consultas SQL en el log.
    | Posibles valores: 0 o 1
    |
    */

    'querylog' => env('DEFINE_QUERYLOG', 0),

    

    /*
    |--------------------------------------------------------------------------
    | Gestion de Fechas SQL
    |--------------------------------------------------------------------------
    |
    | Permite definir los formatos que se usaran para convertir las fechas
    | entre el formato usado en la aplicacion y el formato usado en la base de datos.
    | Posibles valores: se pueden usar los formatos aceptados por DateTime::createFromFormat y DateTime::format
    | por ejemplo: 'd/m/Y', 'Y-m-d', 'd/m/Y H:i', 'Y-m-d H:i:s'.
    |
    */

    'sql_date_formats' => [
        'from' => env('DEFINE.SQL_DATE_FROM', 'd/m/Y'),
        'to'   => env('DEFINE.SQL_DATE_TO', 'Y-m-d')
    ]
];