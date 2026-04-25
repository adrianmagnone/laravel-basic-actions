<?php

namespace Aiglos\Lba\Lib\Pdf;

use Barryvdh\DomPDF\Facade\Pdf;

use Aiglos\Lba\Helpers\DateHelper as MiDate;

class PdfList
{
    protected $titulo = '';
	protected $subtitulos = [];
	protected $headers = [];
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

    public function setSubtitulos($tittles)
    {
        $this->subtitulos = $tittles;
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

    public function output()
	{
        $pdf = PDF::loadView($this->view, [
            'titulo'     => $this->titulo,
            'hoy'        => Midate::today('d/m/Y'),
            'headers'    => $this->headers,
            'data'       => $this->data,
            'subtitulos' => $this->subtitulos
        ])->setPaper($this->papel[$this->indexPapel], $this->orientacion[$this->indexOri]);

        //return $pdf->stream();   

        return $pdf->download($this->filename . '.pdf');   

        // return view($this->view, [
        //     'titulo'     => $this->titulo,
        //     'hoy'        => Midate::today('d/m/Y'),
        //     'headers'    => $this->headers,
        //     'data'       => $this->data,
        //     'subtitulos' => $this->subtitulos
        // ]);
    }

}