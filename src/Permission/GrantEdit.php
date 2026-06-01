<?php

namespace Aiglos\Lba\Permission;

class GrantEdit implements IPermission
{
    public static function editCheck($permiso) : bool
    {
        return true;
    }

    public static function deleteCheck($permiso) : bool
    {
        return true;
    }
}