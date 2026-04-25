<?php

namespace Aiglos\Lba\Actions;

use Aiglos\Lba\Helpers\DateHelper as MiDate;
class FilterBase
{
    protected $filtersKeys;
    protected $requestData;
    protected $executeWithoutValue = false;

    function __construct()
    {
        $this->filtersKeys = $this->setFiltersKeys();
    }

    public function execute(&$query, $requestData)
    {
        $this->requestData = $requestData;
        if ($this->filtersKeys)
        {
            foreach ($this->filtersKeys as $field)
            {
                $funcion = 'filtro' . \ucfirst($field);

                if ($this->executeWithoutValue)
                {
                    if (\method_exists($this, $funcion))
                    {
                        $this->$funcion($query, (isset($requestData[$field])) ? $requestData[$field] : null);
                    }
                }
                else
                {
                    if (isset($requestData[$field]))
                    {
                        if (\is_numeric($requestData[$field]) || ($requestData[$field]))
                        {
                            if (\method_exists($this, $funcion))
                            {
                                $this->$funcion($query, $requestData[$field]);
                            }
                        }
                    }
                }
            }
        }
    }

    public function get($key)
    {
        if (array_key_exists($key, $this->requestData))
        {
            return $this->requestData[$key];
        }

        return false;
    }


    public function existe($key)
    {
        if (array_key_exists($key, $this->requestData))
        {
            return (bool)$this->requestData[$key];
        }

        return false;
    }

    protected function setFiltersKeys()
    {
        return [];
    }

    protected function filtroBetweenDate(&$query, $desde, $hasta, $field)
    {
        $desde = ($desde)
            ? MiDate::fromFormatTo('d/m/Y', $desde, 'Y-m-d')
            : "";
        $hasta = ($hasta)
            ? MiDate::fromFormatTo('d/m/Y', $hasta, 'Y-m-d')
            : "";

        $this->filtroBetween($query, $desde, $hasta, $field);
    }

    protected function filtroBetweenNumber(&$query, $desde, $hasta, $field)
    {
        $desde = (\is_numeric($desde))
                    ? $desde
                    : 0;
        $hasta = (\is_numeric($hasta))
                    ? $hasta
                    : 0;

        $this->filtroBetween($query, $desde, $hasta, $field);
    }

    protected function filtroBetween(&$query, $desde, $hasta, $field)
    {
        if ($desde && $hasta)
        {
            $query->whereBetween($field, [$desde, $hasta]);
        }
        elseif ($desde)
        {
            $query->where($field, '>=', $desde);
        }
        elseif ($hasta)
        {
            $query->where($field, '<=', $hasta);
        }
    }

    protected function filtroBetweenRaw(&$query, $desde, $hasta, $field)
    {
        if ($desde && $hasta)
        {
            $query->whereRaw("{$field} BETWEEN ? AND ?", [$desde, $hasta]);
        }
        elseif ($desde)
        {
            $query->whereRaw("{$field} >= ?", [$desde]);
        }
        elseif ($hasta)
        {
            $query->whereRaw("{$field} <= ?", [$hasta]);
        }
    }

    protected static function getSqlData($sql, $field)
    {
        $results = \Illuminate\Support\Facades\DB::select($sql);

        if ($results)
        {
            return $results[0]->$field;
        }
        return false;
    }

    protected static function getFromTo($field, $value)
    {
        $d = ($value[$field.'Desde']) ? " desde " . $value[$field.'Desde'] : '' ;
        $h = ($value[$field.'Hasta']) ? " hasta " . $value[$field.'Hasta'] : '';
        
        return $d . $h; 
    }
}