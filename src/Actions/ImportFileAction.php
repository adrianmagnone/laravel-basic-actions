<?php

namespace Aiglos\Lba\Actions;

class ImportFileAction
{
    protected $updatedMessage;
    protected $urlImport;
    protected $urlSave;
    protected $urlList;
    protected $jsonResponse;
    protected $skipRows;

    protected $loadView;
    protected $editView;

    protected $model;
    protected $data;
    protected $request;

    protected $cantidad;
    protected $importeNeto;
    protected $importeTotal;

    protected $timeLimit = false;
    protected $memoryLimit = false;

    public function runImport($request = null)
    {
        if ($request)
            $this->request =& $request;

        //$entidad = $this->_createModel();

        $data = $this->aditionalDataForLoad();
        //$data['entity']  = $entidad;
        $data['formFile']  = true;
        $data['saveUrl']   = $this->urlImport;        
        $data['returnUrl'] = $this->urlList;

        return view($this->loadView, $data);
    }

    public function runLoadFile($request)
    {
        if (\is_numeric($this->timeLimit))
            set_time_limit($this->timeLimit);
        if ($this->memoryLimit)
            ini_set('memory_limit', $this->memoryLimit);

        $this->request =& $request;

        $validated = $this->request->validate($this->rulesFile());

        $file = $this->loadFile();

        if ($file)
        {
            $this->data = [];
            $this->cantidad = 0; 
            $this->importeNeto = 0; 
            $this->importeTotal = 0; 

            $this->readFile($file);
            $this->validateData();
            $this->processData($this->skipRows);

            return $this->runForCreate();
        }
    }

    protected function runForCreate()
    {
        $data = $this->aditionalDataForEdit($entidad);
        $data['data']    = $this->data;
        $data['entity']  = $entidad;
        $data['saveUrl'] = $this->urlSave;        
        $data['returnUrl'] = $this->urlList;

        return view($this->editView, $data);
    }

    public function runForSave($request)
    {
        if (\is_numeric($this->timeLimit))
            set_time_limit($this->timeLimit);
        if ($this->memoryLimit)
            ini_set('memory_limit', $this->memoryLimit);

        $this->request =& $request;

        $resultado = false;

        $newData = $this->prepareForValidation();

        if (\is_array($newData))
            $this->request->merge($newData);

        $validated = $this->request->validate($this->rules());

        $records = $this->getRecords();

        \DB::beginTransaction();

        try
        {
            $tabla = $this->getTableName();

            $resultado = \DB::table($tabla)->insert($records);
            
            $this->completeUpdate($resultado, $this->request);
            \DB::commit();
        }
        catch(\Throwable $th)
        {
            \DB::rollback();

            \Log::error($th->getFile());
            \Log::error($th->getLine());
            \Log::error($th->getMessage());
                
            throw new \Exception("Ocurrio un error al guardar los datos.");
        }

        return $this->endUpdate($resultado);
    }

    public function runSaveOneByOne($request)
    {
        if (\is_numeric($this->timeLimit))
            set_time_limit($this->timeLimit);
        if ($this->memoryLimit)
            ini_set('memory_limit', $this->memoryLimit);

        $this->request =& $request;

        $resultado = false;

        $newData = $this->prepareForValidation();

        if (\is_array($newData))
            $this->request->merge($newData);

        $validated = $this->request->validate($this->rules());

        $records = $this->getRecords();

        $resultado = $this->executeSaveOneByOne($records);

        return $this->endUpdate($resultado);
    }

    protected function executeSaveOneByOne(&$records)
    {
        \DB::beginTransaction();

        try
        {
            foreach ($records as $record)
            {
                if ($this->useCreateOwnInModel)
                    $entidad = $this->model::createOwn($record);
                else
                    $entidad = $this->model::create($record);
         
                $this->completeUpdate($entidad, $this->request);    
            } 

            \DB::commit();
            return true;
        }
        catch(\Throwable $th)
        {
            \DB::rollback();

            \Log::error($th->getFile());
            \Log::error($th->getLine());
            \Log::error($th->getMessage());
                
            throw new \Exception("Ocurrio un error al guardar los datos.");
        }
    }

    protected function _createModel()
    {
        return new $this->model;
    }

    protected function loadFile()
    {
        return false;
    }

    protected function aditionalDataForEdit(&$entidad = null)
    {
        return [];
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

    protected function processRecord($record)
    {
        return $record;
    }

    protected function endUpdate($entidad)
    {
        $this->request->session()->flash('update_message', $this->updatedMessage);

        if ($this->jsonResponse)
            return response()->json($this->jsonResponse);

        return redirect($this->urlList);
    }
    
    public function rulesFile(): array
    {
        return [];
    }

    public function rules(): array
    {
        return [];
    }

    public function getRecords() : array
	{
        return [];
    }

    protected function completeUpdate(&$entidad, &$request)
    {
        return false;
    }
    protected function getTableName() : string
    {
        $obj = new $this->model();

        return $obj->getTable();
    }


    use \Aiglos\Lba\Traits\AccessRequest;
}