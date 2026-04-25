<?php

namespace Aiglos\Lba\Traits;

trait AccessRequest
{
    public function __get(string $name): mixed
    {
        return $this->request->{$name};
    }

    public function toDecimal(string $name) : mixed
    {
        $value = $this->request->{$name};

        return ($value) ? \trim(\str_replace (',', '.', \str_replace('.', '', $value))) : null;
    }

    public function valueToDecimal(mixed $value) : mixed
    {
        return ($value) ? \trim(\str_replace (',', '.', \str_replace('.', '', $value))) : null;
    }

    public function intOrNull(string $name) : mixed
    {
        $value = $this->request->{$name};

        return ($value) ? (int)$value : null;
    }

    public function valueOrNull(string $name) : mixed
    {
        $value = $this->request->{$name};

        return ($value) ? $value : null;
    }

    public function valueOrZero(string $name) : mixed
    {
        $value = $this->request->{$name};

        return ($value) ? $value : 0;
    }

    public function onOff(string $name) : mixed
    {
        $value = $this->request->{$name};

        return ($value == 'on') ? 1 : 0;
    }

    public function dateOrNull(string $name) : mixed
    {
        $value = $this->request->{$name};
        
        return ($value)
                ? MiDate::fromFormatTo('d/m/Y', $value, 'Y-m-d')
                : null;
    }

    public function dateTimeOrNull(string $name) : mixed
    {
        $value = $this->request->{$name};
        
        return ($value)
                ? MiDate::fromFormatTo('d/m/Y H:i', $value, 'Y-m-d H:i:s')
                : null;
    }   
}
