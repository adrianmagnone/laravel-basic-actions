<?php

namespace Aiglos\Lba\Excel;

use Aiglos\Lba\Excel\IFormat;
use Aiglos\Lba\Excel\FormatBase;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;

use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class ExcelBase
{
    const GREY  = 'FFEEEEEE';
	const RED   = 'FFFE7474';
	const GREEN = 'FF7CEF7C';
	const BLACK = 'FF000000';

	protected $titulo = '';
	protected $subtitulos = [];
	protected $headers = [];
	protected $footer = [];
	protected $format;
	protected $messageNoData =  'No se encontraron datos';
	protected $firstRowBody = 1;
	protected $functionHighlightRow = false;

	protected $filename = 'listado';
	protected $data;
	protected $excel;
	protected $excelActiveSheet;

	protected $fila = 1;
	protected $previousGroupTittle;


	function __construct($titulo = null, $subtitulos = null, IFormat $format = null)
	{
		$this->titulo = $titulo;
		$this->subtitulos = $subtitulos;

		$this->previousGroupTittle = null;

		$this->excel = new Spreadsheet();

		if ($format)
			$this->format = $format;
		else
			$this->format = new FormatBase();
	}

	public function setHeaders($headers)
	{
		$r1 = range("A","Z");

		$r2 = array_map(function($element){ return 'A' . $element; }, $r1);

		$r3 = array_map(function($element){ return 'B' . $element; }, $r1);

		$celdas = array_merge($r1, $r2, $r3);

		foreach ($headers as $key => $value)
		{
			$headers[$key]['celda'] = array_shift($celdas);
			if (! isset($headers[$key]['format']))
				$headers[$key]['format'] = false;
		}

		$this->headers = $headers;
	}

	public function clearHeaders()
	{
		$this->headers = false;
	}

	public function setData($data)
	{
		$this->data = $data;
	}

	public function setFooterData($footerData)
	{
		$this->footer = $footerData;
	}

	public function setFileName($filename)
	{
		$this->filename = $filename;
	}

	public function setMessageNoData($message)
	{
		$this->messageNoData = $message;
	}

	public function setHighlightRow($function)
	{
		$this->functionHighlightRow = $function;
	}

	public function run($callback = false)
	{
		$this->initDocument();
		$this->renderHeader();
		if ($this->data->count())
		{
			$this->renderBody($callback);
			$this->renderFooter();	
		}
		else
		{
			$this->renderNoData();
		}
	}

	public function initDocument()
	{
		$this->excel->getProperties()
			->setCreator("")
			->setLastModifiedBy("")
			->setTitle($this->titulo)
			->setDescription($this->titulo);

		$this->excel->setActiveSheetIndex(0);
		$this->excelActiveSheet = $this->excel->getActiveSheet();

		$this->renderTitles();
	}

	public function createGroup($callback = false)
	{
		$this->renderHeader();
		$this->renderBody($callback);
		$this->renderFooter();
	}

	public function createGroupWithoutHeader($callback = false)
	{
		$this->renderBody($callback);
		$this->renderFooter();
	}

	public function createGroupWithoutFooter($callback = false)
	{
		$this->renderHeader();
		$this->renderBody($callback);
	}

	public function createGroupWithoutHeaderFooter($callback = false)
	{
		$this->renderBody($callback);
	}

	public function autoRowHeight()
	{
		foreach($this->excelActiveSheet->getRowDimensions() as $rd)
		{
    		$rd->setRowHeight(-1);
		}
	}

	protected function renderTitles()
	{
		if ($this->titulo)
		{
			$this->excelActiveSheet
		    	->setCellValue('A1', $this->titulo)
		    	->getStyle('A1')
				->applyFromArray( $this->format->h1Style() );

		    $this->fila++;
		}

		if (\is_array($this->subtitulos))
		{
			foreach ($this->subtitulos as $value)
			{
				$this->excelActiveSheet
					->setCellValue("A{$this->fila}", $value)
					->getStyle("A{$this->fila}")
					->applyFromArray( $this->format->h2Style() );
				$this->fila++;
			}
			// Dejamos un renglon en blanco para que no se junte los titulos con el encabezado
			$this->fila++;
		}

	}

	protected function renderHeader()
	{
		if (\is_array($this->headers))
		{
			foreach ($this->headers as $value)
			{
				$this->excelActiveSheet
					->setCellValue("{$value['celda']}{$this->fila}", $value['titulo']);
			}

			$rango = $this->rangeCompleteRow();

			$this->excelActiveSheet
				->getStyle($rango)
				->applyFromArray( $this->format->headerStyle() );


			$this->fila++;
		}
	}

	private function rangeCompleteRow()
	{
		$col = "A";
		$lastColumn = end($this->headers);
		if (is_array($lastColumn))
			$col = $lastColumn['celda'];
			
		return "A{$this->fila}:{$col}{$this->fila}";
	}

	protected function renderBody($callback)
	{
		$this->firstRowBody = $this->fila;

		foreach ($this->data as $key => $value)
		{
			$renglon = $callback($value);
			$this->renderRow($renglon);
		}
		$this->setColumnWidth();
	}

	protected function renderRow($renglon)
	{
		if (\is_array($renglon))
		{
			$this->headerGroup($renglon);

			// Generamos la fila de datos
			foreach ($renglon as $key => $value)
			{
				if (\is_array($value))
				{
					$this->renderRow($value);
				}
				else
				{
					if (\array_key_exists($key, $this->headers))
					{
						$letter = $this->headers[$key]['celda'];

						$this->excelActiveSheet
							->setCellValue("{$letter}{$this->fila}", $value);

						if ($this->headers[$key]['format'])
						{
							$this->applyCellFormat(
								$this->excelActiveSheet->getStyle("{$letter}{$this->fila}"),
								$this->headers[$key]['format']
							);
						}
						else
						{
							$this->applyCustomFormatToCell("{$letter}{$this->fila}", $key);
						}
					}
				}
			}

			$rango = $this->rangeCompleteRow();

			$this->highlightRow($renglon, $rango);

			$this->excelActiveSheet
				->getStyle($rango)
				->applyFromArray( $this->format->bodyStyle() );

			$this->fila++;
		}
	}

	protected function renderNoData()
	{
		$this->fila++;
		$this->excelActiveSheet->setCellValue("A{$this->fila}", $this->messageNoData);
		$this->fila++;
	}

	protected function applyCellFormat($style, $formato)
	{
		$formatoAplicable = $this->format->cellFormats($formato);

        if ($formatoAplicable)
        {
		    $style
			    ->getNumberFormat()
			    ->setFormatCode($formatoAplicable['numberFormat']['formatCode']);
        }
	}


	protected function headerGroup(&$renglon)
	{
		if (! isset($renglon['groupHeader']))
			return;

		if ($this->previousGroupTittle == null || $this->previousGroupTittle != $renglon['groupHeader'] )
		{
			$this->fila++;
			$this->excelActiveSheet
				->setCellValue("A{$this->fila}", $renglon['groupHeader']);

			$rango = $this->rangeCompleteRow();

			$this->excelActiveSheet
				->mergeCells($rango);

			$this->excelActiveSheet
				->getStyle($rango)
				->applyFromArray( $this->format->headerGroupStyle() );

			$this->previousGroupTittle = $renglon['groupHeader'];
			$this->fila++;

			$this->renderHeader();
		}
		\unset($renglon['groupHeader']);
	}


	protected function applyCustomFormatToCell($celda, $key)
	{
		$celdaStyle = $this->excel
						->getActiveSheet()
						->getStyle($celda);

		switch ($this->headers[$key]['format']) {
			default:
				break;
		}
	}

	protected function highlightRow($renglon, $rango)
	{
		if (! \is_callable($this->functionHighlightRow))
			return;
		$color = \call_user_func_array($this->functionHighlightRow, [$renglon] );
		if ($color)
		{
			$this->excelActiveSheet
				->getStyle($rango)
				->applyFromArray([
					'fill' => [
						'type' => Fill::FILL_SOLID,
						'color' => ['argb' => $color],
					]
				]);
		}

	}

	protected function setColumnWidth()
	{
		foreach ($this->headers as $key => $value)
		{
			if (\array_key_exists('ancho', $value))
			{
				$letter = $value['celda'];

				$this->excelActiveSheet
					->getColumnDimension("{$letter}")->setWidth( $value['ancho'] );
			}
		}
	}

	protected function renderFooter()
	{
		$this->generateSums();
		if (\is_array($this->footer))
		{
			$this->fila++;
			$rango = "A{$this->fila}:";
			foreach ($this->footer as $key => $value)
			{
				$this->excelActiveSheet
					->setCellValue("A{$this->fila}", $key)
					->setCellValue("C{$this->fila}", $value);
				$this->fila++;
			}
			$ultimaFila = $this->fila - 1;
			$rango .= "C{$ultimaFila}";
			$this->excelActiveSheet
				->getStyle($rango)
				->applyFromArray( $this->format->footerStyle() );
		}
	}

	protected function generateSums()
	{
		foreach ($this->headers as $key => $value)
		{
			if (\array_key_exists('sum', $value))
			{
				$letter = $value['celda'];

				$celda = "{$letter}{$this->fila}";
				$ultimaFila = $this->fila - 1;
				$rango = "{$letter}{$this->firstRowBody}:{$letter}{$ultimaFila}";

				$this->excelActiveSheet
					->setCellValue($celda, "=SUM({$rango})")
					->getStyle($celda)->applyFromArray( array(
						'fill' => [
							'type'  => Fill::FILL_SOLID,
							'color' => ['argb' => 'FFdde9ef'],
						]
					));
			}
		}

		$this->fila++;
	}

	public function output()
	{
		$this->excelActiveSheet->setTitle($this->titulo);

		// Redirect output to a client’s web browser (Excel5)
        \header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		\header("Content-Disposition: attachment;filename=\"{$this->filename}.xlsx\"");
		\header('Cache-Control: max-age=0');
		// If you're serving to IE 9, then the following may be needed
		\header('Cache-Control: max-age=1');

		// If you're serving to IE over SSL, then the following may be needed
		\header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
		\header('Last-Modified: '. \gmdate('D, d M Y H:i:s'). ' GMT'); // always modified
		\header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
		\header('Pragma: public'); // HTTP/1.0

        $objWriter = IOFactory::createWriter($this->excel, 'Xlsx');
		$objWriter->save('php://output');
	}

	/**
	 * Converts a date string to an Excel-compatible date value.
	 *
	 * This method takes a date string in any format recognized by PHP's DateTime class,
	 * converts it to a DateTime object, and then transforms it into an Excel serial number
	 * that can be used in spreadsheet cells.
	 *
	 * @param string $fecha The date string to convert (e.g., '2026-04-25', '25/04/2026')
	 * @return float The Excel date serial number representing the input date
	 *
	 * @example
	 * $excelDate = ExcelBase::toDate('2026-04-25');  // Returns Excel serial number for April 25, 2026
	 * $sheet->setCellValue('A1', $excelDate);
	 */
	public static function toDate($fecha)
	{
		$date = new \Datetime($fecha);
		$time = \gmmktime($date->format('H'), $date->format('i'), $date->format('s'), $date->format('m'), $date->format('d'), $date->format('Y'));
		return \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($time);
	}
}