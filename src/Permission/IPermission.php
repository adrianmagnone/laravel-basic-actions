<?php

namespace Aiglos\Lba\Permission;

interface IPermission
{
    public static function editCheck($permiso) : bool;
    public static function deleteCheck($permiso) : bool;
}
