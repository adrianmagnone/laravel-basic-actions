<?php

namespace Aiglos\Lba\Actions;

class InsertMultipleAction extends EditAction
{
    public function runForSave($request)
    {
        $this->request =& $request;

        $resultado = false;

        $this->currentAction = self::INSERTANDO;

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
        $this->request =& $request;

        $resultado = false;

        $this->currentAction = self::INSERTANDO;

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

    public function getRecords() : array
	{
        return [];
    }
}