<?php

namespace App\Lib\Actions;

use App\Helpers\DateHelper as MiDate;

class ProcessAction
{
    protected $updatedMessage;
    protected $urlList;
    protected $request;
    public $queryData;

    public function run($request, $id = null)
    {
        $this->request =& $request;
        $this->entidad = $this->model::findOrFail($id);

        \DB::beginTransaction();

        try
        {
            foreach ($this->queryData as $datos)
            {
                $this->processEntidad($datos);
            }

            \DB::commit();
        }
        catch(\Throwable $th)
        {
            \DB::rollback();

            \Log::error($th->getFile());
            \Log::error($th->getLine());
            \Log::error($th->getMessage());
            
            throw new \Exception("Ocurrio un error al procesar los datos.");
        }
        return $this->endUpdate();
    }

    protected function getQuery()
    {
        return $this->model->query();
    }

    protected function executeQuery(&$query)
    {
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
    }

    protected function endUpdate()
    {
        $this->request->session()->flash('update_message', $this->updatedMessage);
        return redirect($this->urlList);
    }

    protected function processEntidad(&$entidad)
    {
        return false;
    }
}