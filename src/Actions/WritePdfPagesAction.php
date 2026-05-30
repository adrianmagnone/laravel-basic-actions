<?php

namespace Aiglos\Lba\Actions;

use setasign\Fpdi\Fpdi;
use setasign\Fpdi\PdfReader;

class WritePdfPagesAction
{
    protected $model;
    protected $request;

    protected $entidad;
    protected $pdf;

    protected $archivoBase;
    protected $archivoPie;
    protected $archivoNuevo;
    
    protected $fuente;
    protected $modeloPagina = [
        'orientacion' => 'Portrait',
        'unidad'      => 'mm',
        'tamanio'     => 'A4'
    ];

    public function runCreatePdf(&$request, $idValue = null)
    {
        $this->request = &$request;

        if ($idValue)
            $this->entidad = $this->model::findOrFail($idValue);

        $this->beforeExecute();

        $archivo = $this->execute();

        return response()->download($archivo);
    }  

    public function createFile($idValue)
    {
        $this->entidad = $this->model::findOrFail($idValue);

        $this->beforeExecute();

        return $this->execute();
    }
    
    protected function beforeExecute()
    {
        return;
    }
    
    public function execute()
    {
        try
        {
            $archivo_original = \Storage::path('pdf/'. $this->archivoBase);
            $archivo_nuevo    = \Storage::path('pdf/generados/'. $this->archivoNuevo);

            $cantidadPaginas = 1;

            $fuentePagina  = $this->fuente;

            $this->pdf = new Fpdi($this->modeloPagina['orientacion'], $this->modeloPagina['unidad'], $this->modeloPagina['tamanio']);
            $this->pdf->setSourceFile($archivo_original);

            $nPagina = 1;

            $paginas = $this->getItemsPorPagina();

            foreach ($paginas as $items)
            { 
                $tppl = $this->pdf->importPage($nPagina, PdfReader\PageBoundaries::MEDIA_BOX);
                $this->pdf->AddPage();
			    $this->pdf->useTemplate($tppl, ['adjustPageSize' => true]);
                $this->pdf->SetMargins(0, 0, 0);
                $this->pdf->SetAutoPageBreak(true, 0);

                $this->escribirEnDocumento($items);
            }

            $this->agregarPiePagina();

            $this->pdf->Output($archivo_nuevo, "F");

            return $archivo_nuevo;
        }
        catch(\Throwable $e)
        {
            throw $e;
        }
    }

    protected function agregarPiePagina()
    {
        $paginasPie = $this->getItemsPiePagina();

        if (count($paginasPie) == 0)
            return;

        $archivo = \Storage::path('pdf/' . $this->archivoPie);

        $this->pdf->setSourceFile($archivo);

        foreach ($paginasPie as $datosPie)
        {
            $tppl = $this->pdf->importPage(1, PdfReader\PageBoundaries::MEDIA_BOX);
        
            $this->pdf->AddPage();
		    $this->pdf->useTemplate($tppl, ['adjustPageSize' => true]);
            $this->pdf->SetMargins(0, 0, 0);

        
            $this->escribirEnDocumento($datosPie);
        }
    }


    protected function escribirEnDocumento(&$items)
    {
        foreach ($items as $item)
        {
            $font = (\array_key_exists('fuente', $item)) ? $item['fuente'] : $this->fuente;
            $this->pdf->SetFont($font->nombre, $font->estilo, $font->tamanio);
            $this->pdf->SetTextColor($font->color['r'], $font->color['g'], $font->color['b']);

            $ajusteHorizontal = 0;

            switch ($item['align'])
            {
                case 'R':
                    $ajusteHorizontal = $this->pdf->GetStringWidth($item['valor']);
                    break;

                case 'C':
                    $ajusteHorizontal = $this->pdf->GetStringWidth($item['valor']) / 2;
                    break;
            }

            $tipo = (isset($item['tipo'])) ? $item['tipo'] : 'Write';

            switch  ($tipo)
            {
                case 'Cell':
                    $alto = (isset($item['alto'])) ? $item['alto'] : 5;

                    $this->pdf->SetXY($item['x'] - $ajusteHorizontal, $item['y']);
                    $this->pdf->MultiCell($item['ancho'], $alto, \mb_convert_encoding($item['valor'], 'ISO-8859-1'), 0, $item['align']);
                    break;

                case 'Image':
                    $alto = (isset($item['alto'])) ? $item['alto'] : 5;

                    $this->pdf->SetXY($item['x'] - $ajusteHorizontal, $item['y']);
                    $this->pdf->Image($item['valor']);
                    break;
                
                case 'Write':
                default:
                    $this->pdf->SetXY($item['x'] - $ajusteHorizontal, $item['y']);
                    $this->pdf->Write(0, \mb_convert_encoding($item['valor'], 'ISO-8859-1'));        

                    break;
            }
        }
    }


    protected function getItemsPorPagina()
    {   
        return [];
    }

    protected function getItemsPiePagina() : array
    { 
        return [];
    } 

    public function fillUntil($text, $width, $pattern, $font, $pos = STR_PAD_LEFT)
    {
        $this->pdf->SetFont($font->nombre, $font->estilo, $font->tamanio);

        while($this->pdf->GetStringWidth($text . $pattern) < $width)
        {
            if ($pos == STR_PAD_LEFT)
                $text = $text . $pattern;
            if ($pos ==  STR_PAD_RIGHT)
                $text = $pattern . $text;
        }
        
        return $text;
    }
}