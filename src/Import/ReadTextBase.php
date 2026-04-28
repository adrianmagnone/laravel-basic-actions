<?php

namespace Aiglos\Lba\Lib\Import;

trait ReadTextBase
{
	protected $textData;

    public function readFile($filename)
    {
        $filePath = \Storage::path($filename);

        $this->textData = \fopen($filePath, 'r');
    }

    public function processData($skipRows)
	{
        \rewind($this->textData);

        if ($skipRows > 0)
        {
            for ($i = 0; $i < $skipRows ; $i++) 
                \fgets($this->textData);
        }

        while ($row = \fgets($this->textData))
        {
            $record = $this->transformRecord($row);

            $this->processRecord($record);
        }
    
        \fclose($this->textData);
	}

    public function firstRecord()
    {
        if ($this->textData)
        {
            \rewind($this->textData);
            return \fgets($this->textData);
        }

        return false;
    }

    public function transformRecord(&$record)
    {
        return $record;
    }
}