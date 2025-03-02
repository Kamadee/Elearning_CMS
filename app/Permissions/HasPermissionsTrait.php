<?php

namespace App\Permissions;

use App\Models\Permission;
use App\Models\Role;

trait HasPermissionsTrait
{
  // kiểm tra quyền (permission) của một người dùng
  public function hasPermissionTo($permission)
  {
    if (gettype($permission) == 'object') {
      $permissionObj = $permission;
    } else {
      $permissionObj = Permission::where('guard_name', $permission)->first();
    }
    // dd($permission);
    if (!$permissionObj) return false;
    return $this->hasPermissionThroughRole($permissionObj);
  }

  // kiểm tra xem người dùng có quyền thông qua các vai trò của họ hay không
  public function hasPermissionThroughRole($permission)
  {
    foreach ($permission->roles as $role) {
      if ($this->roles->contains($role)) {
        return true;
      }
    }
    return false;
  }
}
