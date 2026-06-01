<?php

namespace Aiglos\Lba\Permission;

class DeniedEdit implements IPermission
{
    public static function editCheck($permiso) : bool
    {
        return false;
    }

    public static function deleteCheck($permiso) : bool
    {
        return false;
    }
}