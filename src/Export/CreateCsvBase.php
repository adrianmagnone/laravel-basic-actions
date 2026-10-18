<?php

namespace Aiglos\Lba\Export;

use Illuminate\Support\Facades\Storage;

class CreateCsvBase 
{
    protected $data;
    protected $downloadName;
    protected $filename;
    protected $messageNoData = 'No hay datos para generar el archivo.';
    protected $headers;
    protected $filePointer;

    protected string $separator = ",";
    protected string $enclosure = "\"";
    protected string $escape = "\\";
    protected string $eol = "\r\n";

    public function setSeparator(string $value): void
    {
        $this->validateSingleByte($value, 'separador');

        if ($value === $this->enclosure) {
            throw new \InvalidArgumentException('El separador y el enclosure del CSV deben ser distintos.');
        }

        $this->separator = $value;
    }

    public function setEnclosure(string $value): void
    {
        $this->validateSingleByte($value, 'enclosure');

        if ($value === $this->separator) {
            throw new \InvalidArgumentException('El separador y el enclosure del CSV deben ser distintos.');
        }

        $this->enclosure = $value;
    }

    public function setEscape(string $value): void
    {
        if (strlen($value) > 1) {
            throw new \InvalidArgumentException('El escape del CSV debe ser vacío o exactamente un byte.');
        }

        $this->escape = $value;
    }

    public function setEol(string $value): void
    {
        if ($value === '') {
            throw new \InvalidArgumentException('El fin de línea del CSV no puede estar vacío.');
        }

        $this->eol = $value;
    }

    private function validateSingleByte(string $value, string $parameter): void
    {
        if (\strlen($value) !== 1) {
            throw new \InvalidArgumentException("El {$parameter} del CSV debe ser exactamente un byte.");
        }
    }

    private function neutralizeFormula(string $value): string
    {
        // An apostrophe makes spreadsheet applications treat formula prefixes as text.
        $startsWithControlCharacter = \preg_match('/^[\t\r\n]/', $value) === 1;
        $startsWithFormulaCharacter = \preg_match('/^[\x00-\x20]*[=+\-@]/', $value) === 1;

        if ($startsWithControlCharacter || $startsWithFormulaCharacter) {
            return "'" . $value;
        }

        return $value;
    }

    public function setData($data): void
	{
		$this->data = $data;
	}

	public function setFileName($filename): void
	{
        $this->downloadName = $filename;
        Storage::disk('local')->put($filename, '');

        $this->filename = Storage::disk('local')->path($filename);

        if (! \is_writable($this->filename))
        {
            throw new \Exception("El archivo $this->downloadName no se puede escribir.");
        }
	}

	public function setMessageNoData($message): void
	{
		$this->messageNoData = $message;
	}

    public function setHeaders($headers): void
	{
		$this->headers = $headers;
	}

    public function run(callable $callback): void
	{
        if (! \is_countable($this->data))
        {
            throw new \InvalidArgumentException('Los datos deben ser contables.');
        }

        $cantidad = $this->data->count();

		if ($cantidad === 0)
            throw new \Exception($this->messageNoData);
		
        $this->validateSingleByte($this->separator, 'separador');
        $this->validateSingleByte($this->enclosure, 'enclosure');

        if ($this->separator === $this->enclosure) {
            throw new \InvalidArgumentException('El separador y el enclosure del CSV deben ser distintos.');
        }

        if (\strlen($this->escape) > 1) {
            throw new \InvalidArgumentException('El escape del CSV debe ser vacío o exactamente un byte.');
        }

        if ($this->eol === '') {
            throw new \InvalidArgumentException('El fin de línea del CSV no puede estar vacío.');
        }

        if ($this->filename === null || $this->downloadName === null)
        {
            throw new \LogicException('Debe configurarse el nombre del archivo antes de generar el CSV.');
        }

        $this->filePointer = fopen($this->filename, 'w');

        if ($this->filePointer === false)
        {
            throw new \RuntimeException("El archivo ({$this->downloadName}) no se puede abrir.");
        }

        try
        {
            if ($this->headers)
                $this->renderRow($this->headers);

            $posicion = 1;

            foreach ($this->data as $value) 
            {
                $filas = $callback($value);

                if (\is_countable($filas))
                {
                    foreach ($filas as $fila)
                    {
                        $this->renderRow($fila, $posicion == $cantidad);
                    }
                }

                $posicion++;
            }
        }
        finally
        {
            fclose($this->filePointer);
        }
    }

    protected function renderRow($renglon, $ultimaFila = FALSE): void
    {
        if (! \is_array($renglon))
        {
            throw new \UnexpectedValueException('Cada fila del CSV debe ser un arreglo.');
        }

        $renglon = \array_map(
            fn ($valor) => \is_string($valor) ? $this->neutralizeFormula($valor) : $valor,
            $renglon
        );

        if (fputcsv($this->filePointer, $renglon, $this->separator, $this->enclosure, $this->escape, (!$ultimaFila) ? $this->eol : '') === false)
        {
            throw new \RuntimeException("El archivo ($this->downloadName) no se puede escribir.");
        }
    }

    public function output()
	{
        return Storage::disk('local')->download($this->downloadName);
	}
}