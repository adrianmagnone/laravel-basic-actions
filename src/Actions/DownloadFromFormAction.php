<?php

namespace Aiglos\Lba\Actions;

class DownloadFromFormAction
{
    protected $updatedMessage;
    protected $urlImport;
    protected $urlList;
    protected $jsonResponse;

    protected $loadView;
    
    protected $model;
    protected $data;
    protected $request;
    protected $requestData;
    protected $orden;

    protected $customTittles = [];

    protected $searchFields;
    protected $filterClass;
    protected $existFunctionSelectionFilter;

    function __construct($model)
    {
        $this->model = new $model();

        $this->searchFields    = $this->setSearchFields();
        $this->filterClass     = $this->setFilterClass();

        $this->existFunctionSelectionFilter = \method_exists($this, 'setSelectionFilter');
    }

    public function runView($request = null)
    {
        if ($request)
            $this->request =& $request;

        $data = $this->aditionalDataForLoad();
        
        $data['layout']    = 'layouts.form';
        $data['saveUrl']   = $this->urlSave;        
        $data['returnUrl'] = $this->urlList;

        return view($this->loadView, $data);
    }

    public function runDownloadFile($request)
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

        $this->request =& $request;

        $validated = $this->request->validate($this->rules());

        $this->requestData = $this->request->all();

        $query = $this->getQuery();

        $this->filterQuery($query, $this->requestData);

        foreach($this->orden as $orderBy)      
        {
            $query->orderBy(\DB::raw($orderBy['field']), $orderBy['dir']);
        }

        $this->countData = $query->count(); 

        $this->queryData = $query->get();

        $this->exportTittle = $this->generateExportTitles($this->orden[0], $this->requestData, '');

        $this->createFile();
    }

    protected function _createModel()
    {
        return new $this->model;
    }

    protected function getQuery()
    {
        return $this->model->query();
    }


    protected function aditionalDataForLoad()
    {
        return [];
    }

    protected function prepareForValidation()
    {
        return false;
    }

    protected function validateData()
    {
        return true;
    }

    protected function setSearchFields()
    {
        return [];
    }

    protected function setFilterClass()
    {
        return '';
    }

    protected function filterQuery(&$query, $filterValues)
    {
        if ($this->existFunctionSelectionFilter)
        {
            $this->setSelectionFilter($query);
        }

        if (isset($filterValues['search']))
        {
            if ($filterValues['search'] && $this->searchFields)
            {
                $query->where(function (\Illuminate\Database\Eloquent\Builder $whereQuery) use ($filterValues){

                    foreach($this->searchFields as $searchField)
                    {
                        $whereQuery->orWhere($searchField, 'LIKE', "%{$filterValues['search']}%");
                    }

                });
            }
        }

        if ($this->filterClass)
        {
            $this->myFilter = new $this->filterClass();

            $this->myFilter->execute($query, $this->requestData);
        }
    }

    protected function endUpdate()
    {
        $this->request->session()->flash('update_message', $this->updatedMessage);

        if ($this->jsonResponse)
            return response()->json($this->jsonResponse);

        return redirect($this->urlList);
    }
    
    public function rules(): array
    {
        return [];
    }

    use \Aiglos\Lba\Traits\AccessRequest;
}