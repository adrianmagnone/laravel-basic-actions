<?php

namespace Aiglos\Lba\Lib\Import;

trait ReadCsvBase
{
	protected $csvData;

    protected int $length = 0;

    public function readFile($filename)
    {
        $filePath = storage_path('app/' . $filename);

        $this->csvData = \fopen($filePath, 'r');
    }

    public function processData($skipRows, string $delimiter = ",", string $enclosure = '"')
	{
        \rewind($this->csvData);

        if ($skipRows > 0)
        {
            for ($i = 0; $i < $skipRows ; $i++) 
                \fgetcsv($this->csvData, $this->length, $delimiter, $enclosure);
        }

        while ($row = \fgetcsv($this->csvData, $this->length, $delimiter, $enclosure))
        {
            $record = $this->transformRecord($row);

            $this->processRecord($record);
        }
    
        \fclose($this->csvData);
	}

    public function firstRecord()
    {
        if ($this->csvData)
        {
            \rewind($this->csvData);
            return \fgetcsv($this->csvData);
        }

        return false;
    }

    public function transformRecord(&$record)
    {
        return $record;
    }
}