<?php

namespace Aiglos\Lba\Actions;

class EditAction
{
    const INSERTANDO  = 1;
    const EDITANDO    = 2;
    const ELIMINANDO  = 3;
    const CONSULTANDO = 4;

    protected $updatedMessage;
    protected $deletedMessage = 'La acción de borrado se ha realizado exitosamente';
    protected $functionImages = null;
    protected $useCreateOwnInModel = false;
    protected $urlSave;
    protected $urlList;
    protected $urlDelete;
    protected $relationsOfModel = [];
    protected $currentAction;

    protected $editView;
    protected $deleteView;

    protected $model;
    protected string $modelName = '';
    protected $request;

    protected $jsonResponse = [];

    public function runForCreate($request = null)
    {
        $this->currentAction = self::INSERTANDO;
        if ($request)
            $this->request =& $request;

        $entidad = $this->_createModel();

        if (! $this->checkPermission($entidad)) 
            abort(403);

        $data = $this->aditionalDataForEdit($entidad);
        $data['layout']    = 'layouts.form';
        $data['action']    = $this->currentAction;
        $data['entity']    = $entidad;
        $data['saveUrl']   = $this->urlSave;        
        $data['returnUrl'] = $this->urlList;
        if (! isset($data['pageTittle']) )
            $data['pageTittle']= 'Alta de ' . $this->readModelName($entidad);

        return view($this->editView, $data);
    }

    public function runForEdit($id, $request = null)
    {
        $this->currentAction = self::EDITANDO;
        $entidad = $this->model::findOrFail($id);

        if (! $this->checkPermission($entidad)) 
            abort(403);

        if ($request)
            $this->request =& $request;

        $this->validateMyUpdate($entidad);

        $data = $this->aditionalDataForEdit($entidad);
        $data['layout']    = 'layouts.form';
        $data['action']    = $this->currentAction;
        $data['entity']    = $entidad;
        $data['saveUrl']   = $this->urlSave;        
        $data['returnUrl'] = $this->urlList;
        if (! isset($data['pageTittle']) )
            $data['pageTittle']= 'Editar ' . $this->readModelName($entidad);

        return view($this->editView, $data);
    }

    public function runForDelete($id, $request = null)
    {
        $this->currentAction = self::ELIMINANDO;
        $entidad = $this->model::findOrFail($id);

        if (! $this->checkPermission($entidad)) 
            abort(403);

        if ($request)
            $this->request =& $request;

        $this->validateMyDelete($entidad);

        $data = $this->aditionalDataForEdit($entidad);
        $data['layout']    = 'layouts.form';
        $data['action']    = $this->currentAction;
        $data['entity']    = $entidad;
        $data['saveUrl']   = $this->urlDelete;        
        $data['returnUrl'] = $this->urlList;
        if (! isset($data['pageTittle']) )
            $data['pageTittle']= 'Eliminar ' . $this->readModelName($entidad);

        return view($this->deleteView, $data);
    }

    public function runForView($id)
    {
        $this->currentAction = self::CONSULTANDO;
        $entidad = $this->model::findOrFail($id);

        if (! $this->checkPermission($entidad)) 
            abort(403);

        $data = $this->aditionalDataForEdit($entidad);
        $data['layout']    = 'layouts.noform';
        $data['action']    = $this->currentAction;
        $data['entity']    = $entidad;
        $data['returnUrl'] = $this->urlList;
        if (! isset($data['pageTittle']) )
            $data['pageTittle']= 'Consultar ' . $this->readModelName($entidad);

        return view($this->editView, $data);
    }

    protected function checkPermission(&$entidad)
    {
        return true;
    }

    protected function readModelName(&$entidad)
    {
        if ($this->modelName)
            return $this->modelName;

        return \ltrim(\preg_replace('/[A-Z]/', ' $0', class_basename($entidad)));
    }

    public function runForSave($request)
    {
        $this->request =& $request;

        $entidad = false;
        $id = $this->request->input('id', false);

        if (!$id)
        {
            $this->currentAction = self::INSERTANDO;

            $newData = $this->prepareForValidation();

            if (\is_array($newData))
                $this->request->merge($newData);

            $validated = $this->request->validate($this->rules());

            $record = $this->getRecord();
            $record['user_id'] = \Auth::id();

            \DB::beginTransaction();

            try
            {
                if ($this->useCreateOwnInModel)
                    $entidad = $this->model::createOwn($record);
                else
                    $entidad = $this->model::create($record);
             
                $this->completeUpdate($entidad, $this->request);

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
        }
        else
        {
            $entidad = $this->update($id);
        }

        return $this->endUpdate($entidad);
    }

    public function runForSaveDelete($request)
    {
        $this->currentAction = self::ELIMINANDO;

        $this->request =& $request;

        $id = $this->request->input('id', false);

        $entidad = $this->model::findOrFail($id);

        $this->validateMyDelete($entidad);

        \DB::beginTransaction();

        try
        {
            if (\method_exists($this, 'deleteRecord'))
                $this->deleteRecord($entidad);
            else
                $entidad->delete();

            $this->completeDelete($entidad, $this->request);

            \DB::commit();
        }
        catch (\Throwable $th)
        {
            \DB::rollback();

            \Log::error($th->getFile());
            \Log::error($th->getLine());
            \Log::error($th->getMessage());
                
            throw new \Exception("Ocurrio un error al guardar los datos.");
        }

        return $this->endDelete($entidad);
    }

    protected function _createModel()
    {
        return new $this->model;
    }

    protected function aditionalDataForEdit(&$entidad = null)
    {
        return [];
    }

    protected function prepareForValidation()
    {
        return false;
    }

    protected function validateMyUpdate(&$entidad = null)
    {
        if (\method_exists($entidad, 'sePuedeEditar'))
        {
            if (! $entidad->sePuedeEditar())
            {
                throw new \App\Exceptions\InvalidUpdateException($entidad->mensajeValidacion);
            }
        }
    }

    protected function validateMyDelete(&$entidad = null)
    {
        if (\method_exists($entidad, 'sePuedeEliminar'))
        {
            if (! $entidad->sePuedeEliminar())
            {
                throw new \App\Exceptions\InvalidDeleteException($entidad->mensajeValidacion);
            }
        }
    }

    protected function endUpdate(&$entidad)
    {
        if ($this->updatedMessage)
            $this->request->session()->flash('update_message', $this->updatedMessage);

        if ($this->urlList)
            return redirect($this->urlList);

        return response()->json($this->jsonResponse);
    }

    protected function endDelete(&$entidad)
    {
        if ($this->deletedMessage)
            $this->request->session()->flash('update_message', $this->deletedMessage);
        
        return redirect($this->urlList);
    }

    protected function update($id)
    {
        $this->currentAction = self::EDITANDO;

        $entidad = $this->model::findOrFail($id);

        $this->validateMyUpdate($entidad);

        $newData = $this->prepareForValidation();

        if (\is_array($newData))
            $this->request->merge($newData);

        $validated = $this->request->validate($this->rules());

        $record = $this->getRecord();

        \DB::beginTransaction();

        try
        {
            $this->beforeUpdate($entidad, $record);

            if ($record)
            {
                $entidad->fill( $record );
                $entidad->save();
            }

            $this->completeUpdate($entidad, $this->request);

            $observer = $this->getObserverInstance();

            if ($observer)
            {
                $observer->updated($entidad);
            }

            \DB::commit();
        }
        catch (\Throwable $th)
        {
            \DB::rollback();

            \Log::error($th->getFile());
            \Log::error($th->getLine());
            \Log::error($th->getMessage());
                
            throw new \Exception("Ocurrio un error al guardar los datos.");
        }

        return $entidad;
    }

    protected function beforeUpdate(&$entidad, &$record)
    {
        return false;
    }

    protected function completeUpdate(&$entidad, &$request)
    {
        return false;
    }

    protected function completeDelete(&$entidad, &$request)
    {
        return false;
    }

    protected function insertando()
    {
        return $this->currentAction == self::INSERTANDO;
    }

    protected function editando()
    {
        return $this->currentAction == self::EDITANDO;
    }

    protected function eliminando()
    {
        return $this->currentAction == self::ELIMINANDO;
    }

    protected function consultando()
    {
        return $this->currentAction == self::CONSULTANDO;
    }

    protected function getTableName() : string
    {
        $obj = new $this->model();

        return $obj->getTable();
    }

    protected function getObserverInstance()
    {
        return false;
    }

    use \Aiglos\Lba\Traits\AccessRequest;
}