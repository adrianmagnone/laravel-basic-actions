<?php

namespace Aiglos\Lba\Pdf;

use Barryvdh\DomPDF\Facade\Pdf;

use Aiglos\Lba\Helpers\DateHelper as MiDate;

class PdfView
{
    protected $titulo = '';
	protected $subtitulos = [];
	protected $footer = [];

    protected $view;
	protected $filename = 'listado';
    protected $data;

    protected $indexPapel = 'A4';
    protected $indexOri   = 'VERT';
    protected $papel = [
        'A4' => 'a4'
    ];
    protected $orientacion = [
        'VERT' => 'portrait',
        'HORI' => 'landscape'
    ];

    public function setView($view)
    {
        $this->view = $view;
    }

    public function setFileName($name)
    {
        $this->filename = $name;
    }

    public function setData($data)
    {
        $this->data = $data;
    }

    public function setTitulo($tittle)
    {
        $this->titulo = $tittle;
    }

    public function setHeaders(array $headers)
    {
        $this->headers = $headers;
    }

    public function setPaper($size, $ori)
    {
        $this->indexPapel = $size;
        $this->indexOri   = $ori;
    }

    protected function aditionalData()
    {
        return [];
    }

    public function output()
	{
        $data = $this->aditionalData();
        $data['titulo'] = $this->titulo;
        $data['hoy']    = today_formated('d/m/Y');

        $pdf = PDF::loadView($this->view, $data)->setPaper($this->papel[$this->indexPapel], $this->orientacion[$this->indexOri]);

        //return $pdf->stream();   

        return $pdf->download($this->filename . '.pdf');   
    }

}