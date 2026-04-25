<?php

namespace Aiglos\Lba\Actions;

use setasign\Fpdi\Fpdi;
use setasign\Fpdi\PdfReader;

class WritePdfAction
{
    protected $model;
    protected $request;

    protected $entidad;
    protected $pdf;

    protected $archivoBase;
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
    
    protected function beforeExecute()
    {
        return;
    }
    
    public function execute()
    {
        try
        {
            $archivo_original = storage_path('app/pdf/'. $this->archivoBase);
            $archivo_nuevo    = storage_path('app/pdf/generados/'. $this->archivoNuevo);

            $cantidadPaginas = 1;

            $fuentePagina  = $this->fuente;

            $this->pdf = new Fpdi($this->modeloPagina['orientacion'], $this->modeloPagina['unidad'], $this->modeloPagina['tamanio']);
            $this->pdf->setSourceFile($archivo_original);

            $pagina = 1;

            // for ($pagina=1; $pagina <= $cantidadPaginas; $pagina+1)
            // { 
                $tppl = $this->pdf->importPage($pagina, PdfReader\PageBoundaries::MEDIA_BOX);
                $this->pdf->AddPage();
			    $this->pdf->useTemplate($tppl, ['adjustPageSize' => true]);
                $this->pdf->SetMargins(0, 0, 0);
                $this->pdf->SetAutoPageBreak(true, 0);

                $items = $this->getItemsPorPagina($pagina);
                $this->escribirEnDocumento($items);
            // }

            $this->pdf->Output($archivo_nuevo, "F");

            return $archivo_nuevo;
        }
        catch(\Throwable $e)
        {
            dd($e);
            throw $e;
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


    protected function getItemsPorPagina($pagina)
    {   
        $listaItems = [];

        $itemsEncabezado = $template->items;
        $objetoValor     = $this->get($template->clave_objeto_valor);

        foreach ($itemsEncabezado as $itemEncabezado)
        {
            if (\is_array($itemEncabezado->posicion->x))
            {
                switch ($itemEncabezado->tipo_texto)
                {
                    case 'literal':
                        $valor = $itemEncabezado->texto;
                        break;

                    case 'propiedad':
                        $valor = $objetoValor->{$itemEncabezado->texto};
                        break;

                    case 'funcion':
                        $valor = $objetoValor->{$itemEncabezado->texto}();
                        break;
                }

                for ($index=0; $index < \strlen($valor); $index++)
                {
                    $texto = new \stdClass();

                    $texto->fuente = (isset($itemEncabezado->fuente)) ? $itemEncabezado->fuente : $fuenteDocumento;
                    $texto->x      = $itemEncabezado->posicion->x[$index];
                    $texto->y      = $itemEncabezado->posicion->y;
                    $texto->align  = (isset($itemEncabezado->alineacion)) ? $itemEncabezado->alineacion : 'L';
                    $texto->valor  = $valor[$index];

                    $listaItems[] = $texto;
                }
            }
            else
            {
                $texto = new \stdClass();

                $texto->fuente = (isset($itemEncabezado->fuente)) ? $itemEncabezado->fuente : $fuenteDocumento;
                $texto->x      = $itemEncabezado->posicion->x;
                $texto->y      = $itemEncabezado->posicion->y;
                $texto->align  = (isset($itemEncabezado->alineacion)) ? $itemEncabezado->alineacion : 'L';

                switch ($itemEncabezado->tipo_texto)
                {
                    case 'literal':
                        $texto->valor = $itemEncabezado->texto;
                        break;

                    case 'propiedad':
                        $texto->valor = $objetoValor->{$itemEncabezado->texto};
                        break;

                    case 'funcion':
                        $texto->valor = $objetoValor->{$itemEncabezado->texto}();
                        break;
                }

                $listaItems[] = $texto;
            }
        }

        return $listaItems;
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