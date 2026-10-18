<?php

namespace Aiglos\Lba\Export;

use Illuminate\Support\Facades\Storage;

class CreateTextBase 
{
    protected $data;
    protected $downloadName;
    protected $filename;
    protected $messageNoData = 'No hay datos para generar el archivo.';
    protected $headers;
    protected $filePointer;
    protected $contenido;

    protected string $separator = "";
    protected string $eol = "\r\n";

    public function setSeparator(string $value): void
    {
        $this->separator = $value;
    }

    public function setEol(string $value): void
    {
        if ($value === '') {
            throw new \InvalidArgumentException('El fin de línea del CSV no puede estar vacío.');
        }

        $this->eol = $value;
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

        if (! is_writable($this->filename))
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

    public function run(callable $callback)
	{
		if ($this->data->count())
		{
            $this->contenido = [];

            $this->renderHeader();
			$this->renderBody($callback);
		}
		else
		{
            fclose($this->filePointer);
			$this->renderNoData();
		}
    }

    protected function renderHeader()
    {
        if ($this->headers)
            $this->contenido[] = $this->headers;
    }

    protected function renderNoData()
    {
        throw new \Exception($this->messageNoData);
    }

    protected function renderBody($callback)
	{
        if (! \is_countable($this->data))
        {
            throw new \InvalidArgumentException('Los datos deben ser contables.');
        }

        $cantidad = $this->data->count();

		if ($cantidad === 0)
            throw new \Exception($this->messageNoData);
		
        if ($this->eol === '') {
            throw new \InvalidArgumentException('El fin de línea del archivo no puede estar vacío.');
        }

        if ($this->filename === null || $this->downloadName === null)
        {
            throw new \LogicException('Debe configurarse el nombre del archivo.');
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

    protected function renderRow($renglon,  $ultimaFila = FALSE): void
    {
        if (! \is_array($renglon))
        {
            throw new \UnexpectedValueException('Cada fila debe ser un arreglo.');
        }

        if ($ultimaFila)
            $renglon =  \implode($this->separator, $renglon);
        else
            $renglon =  \implode($this->separator, $renglon) . $this->eol;

        if (fwrite($this->filePointer, $renglon) === FALSE)
        {
            throw new \RuntimeException("El archivo ($this->downloadName) no se puede escribir.");
        }
    }

    public function output()
	{
        return Storage::disk('local')->download($this->downloadName);
	}
}