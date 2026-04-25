<?php

namespace Aiglos\Lba\Lib\Import;

trait ReadExcelBase
{
    protected $excel;
	protected $sheetData;

    public function readFile($filename)
    {
        $this->excel = \PhpOffice\PhpSpreadsheet\IOFactory::load(storage_path('app/'. $filename));

        $this->sheetData = $this->excel->getActiveSheet()->toArray(null, true, true, true);
    }

    public function processData($skipRows)
	{
        if ($skipRows > 0)
        {
            \array_splice($this->sheetData, 0, $skipRows);
        }

		foreach ($this->sheetData as $key => $value)
		{
            $this->processRecord($value);
		}
	}
}