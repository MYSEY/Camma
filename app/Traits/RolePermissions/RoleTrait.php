<?php

namespace App\Traits\RolePermissions;

use Backpack\PermissionManager\app\Models\Role;

trait RoleTrait
{
    // *** Create roles
    protected function roles(string $name)
    {
        $returnRole = null;
        switch (strtolower($name)) {
            case ('developer'):
                $returnRole = Role::firstOrCreate(['name' => 'Developer'])->id;
                break;
            case ('admin'):
                $returnRole = Role::firstOrCreate(['name' => 'Admin'])->id;
                break;
            case ('loan'):
                $returnRole = Role::firstOrCreate(['name' => 'Loan'])->id;
                break;
            case ('loan admin'):
                $returnRole = Role::firstOrCreate(['name' => 'Loan Admin'])->id;
                break;
            case ('master loan admin'):
                $returnRole = Role::firstOrCreate(['name' => 'Master Loan Admin'])->id;
                break;
            case ('fif'):
                $returnRole = Role::firstOrCreate(['name' => 'FIF'])->id;
                break;
            case ('fif admin'):
                $returnRole = Role::firstOrCreate(['name' => 'FIF Admin'])->id;
                break;
            case ('master fif admin'):
                $returnRole = Role::firstOrCreate(['name' => 'Master FIF Admin'])->id;
                break;
            case ('trading'):
                $returnRole = Role::firstOrCreate(['name' => 'Trading'])->id;
                break;
            case ('trading admin'):
                $returnRole = Role::firstOrCreate(['name' => 'Trading Admin'])->id;
                break;
            case ('master trading admin'):
                $returnRole = Role::firstOrCreate(['name' => 'Master Trading Admin'])->id;
                break;
            case ('marketing team'):
                $returnRole = Role::firstOrCreate(['name' => 'Marketing Team'])->id;
                break;
            default:
                return $returnRole;
        }
        return $returnRole;
    }
}
