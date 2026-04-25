<?php

namespace Aiglos\Lba\Actions;


class EmptySelectAction
{
    protected $viewList;
    protected $fieldSustitute;

    protected $model;
    protected $idField = 'id';
    protected $request;
    // Almacenamos todos los datos que vienen en $request
    protected $requestData;

    public $queryData;
    public $oneData;
    public $countData;


    function __construct($data)
    {
        $this->oneData = $data;
        $this->queryData = false;
    }

    public function requestKey()
    {
        return '';
    }

    public function viewList()
    {
        return view($this->viewList);
    }

    public function run(&$request, $idValue = null)
    {
        $this->request = &$request;
        if ($idValue !== null)
        {
            return $this->exportRecord();
        }
        else
        {
            return $this->exportDataTable();
        }
    }

    public function exportDataTable()
    {
        if ($this->request)
        {
            $result = new \stdClass();
            $result->draw = (int) $this->request->query('draw');
            $result->recordsTotal = (int)$this->countData;
            $result->recordsFiltered = (int)$this->countData;
            $result->data= [];
        }
        return \json_encode($result);
    }

    public function exportRecord()
    {
        $datos = $this->oneData;
        if ($datos)
        {
            return \json_encode($datos);
        }
            
        return \json_encode(['id' => '', 'descripcion' => '' ]);
    }
}
