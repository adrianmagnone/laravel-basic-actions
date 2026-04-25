<?php

namespace Aiglos\Lba\Actions;

class UploadFileAction
{
    protected $updatedMessage;
    protected $urlImport;
    protected $urlList;
    protected $jsonResponse;

    protected $loadView;
    
    protected $model;
    protected $data;
    protected $request;

    public function runUpload($request = null)
    {
        if ($request)
            $this->request =& $request;


        $data = $this->aditionalDataForLoad();
        $data['formFile']  = true;
        $data['saveUrl']   = $this->urlImport;        
        $data['returnUrl'] = $this->urlList;

        return view($this->loadView, $data);
    }

    public function runLoadFile($request)
    {
        $this->request =& $request;

        $validated = $this->request->validate($this->rules());

        $file = $this->loadFile();

        if ($file)
        {
            return $this->endUpdate();
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