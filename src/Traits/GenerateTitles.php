<?php

namespace Aiglos\Lba\Traits;

trait GenerateTitles
{
    public function generateExportTitles($order, $filterData, $search)
    {
        $this->createTitles();

        $titulos = [];

        // TITULOS PERSONALIZADOS
        
        foreach ($this->customTittles as $field => $funcTitulo)
        {
            if (\is_callable($funcTitulo))
            {
                $titulos[] = $funcTitulo();
            }
        }

        // FILTROS
        if (\count($filterData))
        {
            if ($this->filterTittleSustitute && $filterData)
            {
                foreach ($this->filterTittleSustitute as $field => $confSustituye)
                {
                    if ( \array_key_exists($field, $filterData) && \is_callable($confSustituye))
                    {
                        if ($this->checkIfHaveValue($filterData[$field]))
                        {
                            $tituloEspecifico = $confSustituye($filterData[$field]);

                            if ($tituloEspecifico)                         
                                $titulos[] = $tituloEspecifico;
                        }
                    }
                }
            }
        }

        // BUSQUEDA
        if (\trim($search))
        {
            $titulos[] = "Que contenga el valor: {$search}";
        }

        // ORDEN
        if (isset($order['field']))
        {
            $columnaOrden = $order['field'];
            if ( \array_key_exists($columnaOrden, $this->ordenTittleSustitute) )
            {
                $columnaOrden = $this->ordenTittleSustitute[$columnaOrden];
            }
            $direccion = ($order['dir'] == 'desc') ? ' en forma descendente' : '';
        
            $titulos[] = "Ordenado por {$columnaOrden} {$direccion}";
        }

        return $titulos;
    }

    protected function checkIfHaveValue($data)
    {
        if (\is_array($data))
        {
            $keys = \array_keys($data);
            foreach ($keys as $clave)
            {
                if (! empty($data[$clave]))
                {
                    return true;
                }
            }
            return false;
        }
        if (\is_object($data))
        {
            return true;
        }
        return (! empty($data));
    }

    protected function getSqlData($sql, $field)
    {
        $results = \DB::select($sql);

        if ($results)
        {
            return $results[0]->$field;
        }
        return false;
    }

    protected function getFromTo($field, $value)
    {
        $d = ($value[$field.'Desde']) ? " desde " . $value[$field.'Desde'] : '' ;
        $h = ($value[$field.'Hasta']) ? " hasta " . $value[$field.'Hasta'] : '';
        
        return $d . $h; 
    }
}
