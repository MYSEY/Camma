<?php

namespace App\Traits\RolePermissions;

use App\Traits\RolePermissions\RoleTrait;
use Backpack\PermissionManager\app\Models\Permission;

trait PermissionTrait
{
    use RoleTrait;

    // *** User
    public function user($seed = false)
    {
        if ($seed) {
            Permission::firstOrCreate(['name' => 'list user'])->roles()->sync([
                $this->roles('developer'), $this->roles('admin'), $this->roles('loan'), $this->roles('trading')
            ]);
            Permission::firstOrCreate(['name' => 'create user'])->roles()->sync([
                $this->roles('developer'), $this->roles('admin'), $this->roles('loan'), $this->roles('trading')
            ]);
            Permission::firstOrCreate(['name' => 'show user'])->roles()->sync([
                $this->roles('developer'), $this->roles('admin'), $this->roles('loan'), $this->roles('trading')
            ]);
            Permission::firstOrCreate(['name' => 'update user'])->roles()->sync([
                $this->roles('developer'), $this->roles('admin'), $this->roles('loan'), $this->roles('trading')
            ]);
            Permission::firstOrCreate(['name' => 'delete user'])->roles()->sync([
                $this->roles('developer'), $this->roles('admin'), $this->roles('loan'), $this->roles('trading')
            ]);
            Permission::firstOrCreate(['name' => 'list device'])->roles()->sync([
                $this->roles('developer'), $this->roles('admin'), $this->roles('trading')
            ]);
            Permission::firstOrCreate(['name' => 'list device token'])->roles()->sync([
                $this->roles('developer'), $this->roles('admin'), $this->roles('trading')
            ]);
        }
    }
}
