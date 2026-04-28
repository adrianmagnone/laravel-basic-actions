<?php

namespace Aiglos\Lba\Actions;

class SelectAction
{
    const SEARCH_ENTIRE_PHRASE = 1;
    const SEARCH_WORD_BY_WORD  = 2;

    protected $viewList;
    protected $fieldSustitute;
    protected $ordenTittleSustitute = [];
    protected $filterTittleSustitute = [];
    protected $customTittles = [];
    protected $searchFields;
    protected $customFilterFields;
    protected $aditionalData;
    protected $aditionalOrder;

    protected $typeSearch = self::SEARCH_WORD_BY_WORD;

    protected $builderFilter;

    protected $model;
    protected $idField = 'id';
    protected $request;
    // Almacenamos todos los datos que vienen en $request
    protected $requestData;
    protected $filterClass = '';
    protected $myFilter;

    public $queryData;
    public $countData;
    public $exportTittle = '';

    protected $existFunctionSelectionFilter;
    protected $existFunctionCreateRecord;

    protected $permisoEdit = false;
    protected $permisoDelete = false;

    function __construct($model)
    {
        $this->model = new $model();

        $this->fieldSustitute  = $this->setFieldSustitute();
        $this->searchFields    = $this->setSearchFields();
        $this->customFilterFields = $this->setCustomFilters();
        $this->aditionalOrder  = $this->setAditionalOrderFields();
        $this->aditionalData   = $this->setAditionalData();
        $this->filterClass     = $this->setFilterClass();

        $this->existFunctionSelectionFilter = \method_exists($this, 'setSelectionFilter');
        $this->existFunctionCreateRecord = \method_exists($this, 'createRecord');
    }

    public function requestKey()
    {
        return '';
    }

    public function setSearchByPhrase()
    {
        $this->typeSearch = self::SEARCH_ENTIRE_PHRASE;
    }

    public function setSearchByWord()
    {
        $this->typeSearch = self::SEARCH_WORD_BY_WORD;
    }

    protected function setFieldSustitute()
    {
        return [];
    }

    protected function setSearchFields()
    {
        return [];
    }

    protected function setAditionalOrderFields()
    {
        return ['id'];
    }

    protected function setCustomFilters()
    {
        return [];
    }

    protected function setAditionalData()
    {
        return [];
    }

    protected function aditionalDataForList()
    {
        return [];
    }

    protected function setFilterClass()
    {
        return '';
    }

    protected function getQuery()
    {
        return $this->model->query();
    }

    public function viewList()
    {
        return view($this->viewList)->with($this->aditionalDataForList());
    }

    public function run(&$request, $idValue = null)
    {
        $this->request = &$request;
        if ($idValue !== null)
        {
            $this->runFromId($idValue);
            return $this->exportRecord();
        }
        else
        {
            $this->requestData = $this->request->all();

            // Guardamos los datos en la sesion
            session([$this->requestKey() => $this->requestData]);

            $this->runFromRequest();

            return $this->exportDataTable();
        }
    }

    public function runForJson(&$request, $idValue = null)
    {
        $this->request = &$request;
        
        $query = $this->getQuery();

        $this->filterQuery($query, $this->request->all());

        $this->executeQuery($query);

        $result = new \stdClass();
        $result->data= [];

        foreach ($this->queryData as $datos)
        {
            if ($this->existFunctionCreateRecord)
                $record = $this->createRecord($datos);
            else
                $record = $datos;

            if(! \property_exists($record, 'puedeEditar'))
                $record->puedeEditar = 0;
            if(! \property_exists($record, 'puedeEliminar'))
                $record->puedeEliminar = 0;

            $result->data[] = $record;
        }

        return \json_encode($result->data);
    }

    public function runForExport(&$request)
    {
        $this->request = &$request;

        $this->requestData = session($this->requestKey());

        $options = $this->createListOptions();

        $applyOrder =  $this->orderLista($options->orden);

        $query = $this->getQuery();

        $this->filterQuery($query, $options->filtro);

        $this->countData = $query->count(); 

        foreach($applyOrder as $orderBy)      
        {
            $query->orderBy(\DB::raw($orderBy[0]), $orderBy[1]);
        }

        $this->executeQuery($query);
        
        $this->exportTittle = $this->generateExportTitles($options->orden, $this->requestData, $options->filtro['search']);

        return $this->createFile();
    }

    public function setInitForExcel($request)
    {
        \set_time_limit(0);
        \ini_set('memory_limit', config('lba.exportar.memoria_excel'));

        $cookieConfig = config('lba.exportar.cookie_excel');

        \setcookie(
            $cookieConfig['name'],
            $request->query($cookieConfig['value']),
            time()+ $cookieConfig['expire'],
            $cookieConfig['path'],     
            $cookieConfig['domain'],   
            $cookieConfig['secure'],        
            $cookieConfig['httponly']      
        );
    }

    public function setInitForPdf($request)
    {
        \set_time_limit(0);
        \ini_set('memory_limit', config('lba.exportar.memoria_excel'));
    }

    public function exportDataTable($onlyData = false)
    {
        if ($this->request)
        {
            $result = new \stdClass();
            $result->draw = (int) $this->request->query('draw');
            $result->recordsTotal = (int)$this->countData;
            $result->recordsFiltered = (int)$this->countData;
            $result->data= [];

            if (\Auth::guest())
            {
                $puedeEditar   = false;
                $puedeEliminar = false;
            }
            else
            {
                $puedeEditar   = ($this->permisoEdit)
                                    ? ((\is_array($this->permisoEdit)) ? \Auth::user()->canAny($this->permisoEdit) : \Auth::user()->can($this->permisoEdit))
                                    : 0;
                $puedeEliminar = ($this->permisoDelete)
                                    ? ((\is_array($this->permisoDelete)) ? \Auth::user()->canAny($this->permisoDelete) : \Auth::user()->can($this->permisoDelete))
                                    : 0;
            }

            foreach ($this->queryData as $datos)
            {
                if ($this->existFunctionCreateRecord)
                    $record = $this->createRecord($datos);
                else
                    $record = $datos;

                if (\is_object($record))
                {
                    $record->puedeEditar   = (\property_exists($record, 'puedeEditar'))
                                            ? (int)($puedeEditar && $record->puedeEditar)
                                            : (int)$puedeEditar;
                    $record->puedeEliminar = (\property_exists($record, 'puedeEliminar'))
                                            ? (int)($puedeEliminar && $record->puedeEliminar)
                                            : (int)$puedeEliminar;
                    $result->data[] = $record;
                }
            }
        }

        return \json_encode($result);
    }

    public function exportRecord()
    {
        $datos = $this->queryData->first();
        if ($datos)
        {
            $record = (\method_exists($this, 'createRecord'))
                        ? $this->createRecord($datos)
                        : $datos;            
            return \json_encode($record);
        }
            
        return \json_encode(['id' => '', 'descripcion' => '' ]);
    }

    protected function runFromId($idValue)
    {
        $query = $this->getQuery();

        $this->createIdFilter($query, $idValue);

        $this->executeQuery($query);
    }

    protected function runFromRequest()
    {
        $options = $this->createListOptions();

        $applyOrder =  $this->orderLista($options->orden);

        $query = $this->getQuery();

        $this->filterQuery($query, $options->filtro);

        $this->countData = $query->count();

        foreach($applyOrder as $orderBy)      
        {
            $query->orderBy(\DB::raw($orderBy[0]), $orderBy[1]);
        }
        
        $query->skip($options->desde_reg)->take($options->cant_pagina);

        $this->executeQuery($query);
    }

    protected function beforeExecuteQuery(&$query)
    {
        return false;
    }

    protected function executeQuery(&$query)
    {
        $this->beforeExecuteQuery($query);

        if ((int)config('lba.querylog'))
        {
            \DB::enableQueryLog();

            $this->queryData = $query->get();

            \Log::debug(\DB::getQueryLog());
        }
        else
        {
            $this->queryData = $query->get();
        }

        $this->afterExecuteQuery();
    }

    protected function afterExecuteQuery()
    {
        return false;
    }

    protected function createListOptions()
    {
        $options = new \stdClass();

        if ($this->requestData)
        {
            $options->cant_pagina = (isset($this->requestData['length'])) ? ($this->requestData['length']) : 0;
            $options->desde_reg   = (isset($this->requestData['length'])) ? (int)$this->requestData['start'] : 0;

            $columns = $this->requestData['columns'];
            $order   = isset($this->requestData['order'])  ? $this->requestData['order']  : false;
            $search  = isset($this->requestData['search']) ? $this->requestData['search'] : [ 'value' => ''];

            if ($order)
            {
                $options->orden = [
                    'field' => $columns[$order[0]['column']]['name'],
                    'dir'   => $order[0]['dir']
                ];
            }
            else
            {
                $options->orden =[];
            }

            $options->filtro = [
                'search' => $search['value'],
                'custom' => [],
            ];

            if ($this->aditionalData)
            {
                foreach ($this->aditionalData as $field)
                {
                    $options->filtro['custom'][$field] =  (isset($this->requestData[$field]))
                                                                ? $this->requestData[$field]
                                                                : 0;
                }
            }
        }
        else
        {
            $options->cant_pagina = 10;
            $options->desde_reg   = 0;
            $options->orden       = ['field'  => 'id', 'dir' => 'ASC' ];
            $options->filtro      = ['search' => '', 'custom' => [], ];
        }

        return $options;
    }

    
    protected function orderLista($order)
    {
        $ordenacion = [];

        if ($order)
        {
            $columnaOrden = $order['field'];
            if (\array_key_exists($columnaOrden, $this->fieldSustitute) )
            {
                $columnaOrden = $this->fieldSustitute[$columnaOrden];
            }

            if (\is_array($columnaOrden))
            {
                foreach ($columnaOrden as $col)
                {
                    $ordenacion[] = [$col, $order['dir']];    
                }
            }
            else
            {
                $ordenacion[] = [$columnaOrden, $order['dir']];
            }
            
            foreach ($this->aditionalOrder as $individualAditionalOrder)
            {
                if (\array_key_exists($individualAditionalOrder, $this->fieldSustitute) )
                    $individualAditionalOrder = $this->fieldSustitute[$individualAditionalOrder];

                $existeClave = (\is_array($columnaOrden))
                                ? \in_array($individualAditionalOrder, $columnaOrden)
                                : ($individualAditionalOrder == $columnaOrden);
                
                if (! $existeClave)
                    $ordenacion[] = [ $individualAditionalOrder, isset($order['dir']) ? $order['dir'] : 'asc' ];
            }
        }

        return $ordenacion;
    }

    protected function filterQuery(&$query, $filterValues)
    {
        if ($this->existFunctionSelectionFilter)
        {
            $this->setSelectionFilter($query);
        }

        if (isset($filterValues['search']) && $this->searchFields)
        {
            $listFilters = \explode(' ', $filterValues['search']);

            foreach($listFilters as $filter)
            {
                $query->where(function (\Illuminate\Database\Eloquent\Builder $whereQuery) use ($filter){
                    foreach($this->searchFields as $searchField)
                    {
                        $whereQuery->orWhere($searchField, 'LIKE', "%{$filter}%");
                    }
                });
            }
        }

        if ($this->filterClass)
        {
            $this->myFilter = new $this->filterClass();

            $this->myFilter->execute($query, $this->requestData);

            return;
        }
    }

    protected function createIdFilter(&$query, $idValue)
    {
        $query->where('id', (int)$idValue);
    }
}
