<?php

namespace App\Services;

use App\Models\User;
use App\Models\RolePermission;
use App\Models\Permission;
use App\Helpers\Helper;
use App\Models\Role;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\Datatables\Datatables;

class UserServices
{
  public function getRoleList()
  {
    $roleList = Role::with(['permissions'])->get();
    return $roleList;
  }

  public function processUpdateSelf($formData)
  {
    try {
      $id = Auth::user()->id;
      $accountData = [
        'username' => $formData['username'],
        'updated_at' => Carbon::now()
      ];
      if (isset($formData['password'])) {
        $accountData['password'] = Hash::make($formData['password']);
      }
      DB::beginTransaction();
      User::where('id', $id)->update($accountData);
      DB::commit();
      return [
        'status' => true,
        'id' => $id,
        'message' => 'success'
      ];
    } catch (\Exception $e) {
      DB::rollBack();
      return [
        'status' => false,
        'message' => $e->getMessage()
      ];
    }
  }

  public function getAdmins()
  {
    $result = User::with('roles')->get();

    return $result;
  }

  public function formatAdminDatatables($data)
  {
    return Datatables::of($data)
      ->addIndexColumn()
      ->addColumn('role_name', function ($row) {
        return optional($row->roles->first())->role_name;
      })
      ->addColumn('description', function ($row) {
        return optional($row->roles->first())->description;
      })
      ->addColumn('action', function ($row) {
        $action = '';
        if (Helper::checkPermission('admin.edit')) {
          $action .= '<a href="/admin/detail/' . $row->id . '" class="edit btn btn-primary btn-sm">' . __('admin.detail_admin') . '</a>';
        }
        if (Helper::checkPermission('admin.delete')) {
          $action .= '<button data-id="' . $row->id . '" data-name="' . $row->username . '" class="btn-delete-user btn btn-danger btn-sm">' . __('admin.delete_admin') . '</button>';
        }
        return $action;
      })
      ->rawColumns(['role_name', 'role_description', 'action'])
      ->make(true);
  }

  public function processUpdateAccount($id, $formData)
  {
    try {
      $accountData = [
        'username' => $formData['username'],
        'updated_at' => Carbon::now()
      ];
      if (isset($formData['password'])) {
        $accountData['password'] = Hash::make($formData['password']);
      }

      DB::beginTransaction();
      User::where('id', $id)->update($accountData);

      $roleSelected = isset($formData['roleSelected']) ? $formData['roleSelected'] : [];
      $dataUserRoles = [];
      foreach ($roleSelected as $role) {
        $dataUserRoles[] = [
          'user_id' => (int)$id,
          'role_id' => (int)$role,
          'created_at' => Carbon::now(),
          'updated_at' => Carbon::now()
        ];
      }
      $currentUser = User::find($id);
      $currentUser->roles()->detach();
      $currentUser->roles()->attach($dataUserRoles);

      DB::commit();
      return [
        'status' => true,
        'id' => $id,
        'message' => 'success'
      ];
    } catch (\Exception $e) {
      DB::rollBack();
      return [
        'status' => false,
        'message' => $e->getMessage()
      ];
    }
  }

  public function processCreateAccount($formData)
  {
    try {
      $accountData = [
        'username' => $formData['username'],
        'password' => Hash::make($formData['password']),
        'created_at' => Carbon::now(),
        'updated_at' => Carbon::now()
      ];

      DB::beginTransaction();
      $id = User::insertGetId($accountData);

      $roleSelected = isset($formData['roleSelected']) ? $formData['roleSelected'] : [];
      $dataUserRoles = [];
      foreach ($roleSelected as $role) {
        $dataUserRoles[] = [
          'user_id' => (int)$id,
          'role_id' => (int)$role,
          'created_at' => Carbon::now(),
          'updated_at' => Carbon::now()
        ];
      }
      $currentUser = User::find($id);
      $currentUser->roles()->detach();
      $currentUser->roles()->attach($dataUserRoles);

      DB::commit();
      return [
        'status' => true,
        'id' => $id,
        'message' => 'success'
      ];
    } catch (\Exception $e) {
      DB::rollBack();
      return [
        'status' => false,
        'message' => $e->getMessage()
      ];
    }
  }

  public function processDeleteUser($id)
  {
    try {
      DB::beginTransaction();
      $user = User::find($id);
      $user->roles()->detach();
      $user->status = array_keys(Config::get('constants.user_status'))[0];
      $user->delete();
      DB::commit();
      return [
        'status' => true,
        'message' => 'success'
      ];
    } catch (\Exception $e) {
      DB::rollback();
      return [
        'status' => false,
        'message' => $e->getMessage()
      ];
    }
  }

  public function formatRoleDatatables($data)
  {
    return Datatables::of($data)
      ->addIndexColumn()
      ->addColumn('action', function ($row) {
        $action = '';
        if (Helper::checkPermission('role.edit')) {
          $action .= '<a href="/role/detail/' . $row->id . '" class="edit btn btn-primary btn-sm mr-1">' . __('role.detail_role') . '</a>';
        }
        $action .= '<button data-id="' . $row->id . '" data-name="' . $row->role_name . '" class="btn-delete-role btn btn-danger btn-sm">' . __('role.delete_role') . '</button>';

        return $action;
      })
      ->rawColumns(['action'])
      ->make(true);
  }

  public function getGroupByPermission()
  {
    $permissionAll = Permission::all();
    $data = [];
    foreach ($permissionAll as $permission) {
      $guardName = $permission->guard_name;
      $key = explode(".", $guardName)[0];
      if (isset($data[$key])) {
        $data[$key][] = $permission;
      } else {
        $data[$key] = [$permission];
      }
    }

    return $data;
  }

  public function processCreateRole($formData)
  {
    try {
      $roleData = [
        'role_name' => $formData['roleName'],
        'description' => $formData['roleDescription'],
        'created_at' => Carbon::now(),
        'updated_at' => Carbon::now()
      ];

      DB::beginTransaction();
      $id = Role::insertGetId($roleData);

      if (isset($formData['permissionSelected'])) {
        $permissionSelected = json_decode($formData['permissionSelected'], true);
        $dataRolePermission = [];
        foreach ($permissionSelected as $permission) {
          $dataRolePermission[] = [
            'role_id' => $id,
            'permission_id' => (int)$permission['permission'],
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
          ];
        }
        if (count($dataRolePermission) > 0) {
          RolePermission::insert($dataRolePermission);
        }
      }

      DB::commit();
      return [
        'status' => true,
        'id' => $id,
        'message' => 'success'
      ];
    } catch (\Exception $e) {
      DB::rollBack();
      return [
        'status' => false,
        'message' => $e->getMessage()
      ];
    }
  }

  public function getRoleDetail($id)
  {
    $role = Role::with(['permissions'])->find($id);
    return $role;
  }

  public function processUpdateRole($id, $formData)
  {
    try {
      $roleData = [
        'role_name' => $formData['roleName'],
        'description' => $formData['roleDescription'],
        'updated_at' => Carbon::now()
      ];

      DB::beginTransaction();
      Role::where('id', $id)->update($roleData);

      if (isset($formData['permissionSelected'])) {
        $permissionSelected = json_decode($formData['permissionSelected'], true);
        $dataRolePermission = [];
        foreach ($permissionSelected as $permission) {
          $dataRolePermission[] = [
            'role_id' => (int)$id,
            'permission_id' => (int)$permission['permission'],
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
          ];
        }
        $currentRole = Role::find($id);
        $currentRole->permissions()->detach();
        RolePermission::insert($dataRolePermission);
      }

      DB::commit();
      return [
        'status' => true,
        'id' => $id,
        'message' => 'success'
      ];
    } catch (\Exception $e) {
      DB::rollBack();
      return [
        'status' => false,
        'message' => $e->getMessage()
      ];
    }
  }

  public function processDeleteRole($id)
  {
    try {
      DB::beginTransaction();
      $role = Role::find($id);
      $role->permissions()->detach();
      $role->users()->detach();
      $role->delete();
      DB::commit();
      return [
        'status' => true,
        'message' => 'success'
      ];
    } catch (\Exception $e) {
      DB::rollBack();
      return [
        'status' => false,
        'message' => $e->getMessage()
      ];
    }
  }

  public function getPermissionList()
  {
    $queries = Permission::select('*');
    return $queries->get();
  }

  public function formatPermissionDatatables($data)
  {
    return Datatables::of($data)
      ->addIndexColumn()
      ->addColumn('action', function ($row) {
        $action = '';
        if (Helper::checkPermission('permission.edit')) {
          $action .= '<a href="/permission/detail/' . $row->id . '" class="edit btn btn-primary btn-sm mr-1">' . __('permission.detail_permission') . '</a>';
        }
        if (Helper::checkPermission('permission.delete')) {
          $action .= '<button data-id="' . $row->id . '" data-name="' . $row->permission_name . '" class="btn-delete-permission btn btn-danger btn-sm">' . __('permission.delete_permission') . '</button>';
        }
        return $action;
      })
      ->rawColumns(['action'])
      ->make(true);
  }

  public function processCreatePermission($formData)
  {
    try {
      $permissionData = [
        'permission_name' => strtoupper($formData['permissionName']),
        'guard_name' => strtolower($formData['permissionName']),
        'description' => $formData['permissionDescription'],
        'created_at' => Carbon::now(),
        'updated_at' => Carbon::now()
      ];

      DB::beginTransaction();
      $id = Permission::insertGetId($permissionData);

      DB::commit();
      return [
        'status' => true,
        'id' => $id,
        'message' => 'success'
      ];
    } catch (\Exception $e) {
      DB::rollBack();
      return [
        'status' => false,
        'message' => $e->getMessage()
      ];
    }
  }

  public function processUpdatePermission($id, $formData)
  {
    try {
      $permissionData = [
        'permission_name' => strtoupper($formData['permissionName']),
        'guard_name' => strtolower($formData['permissionName']),
        'description' => $formData['permissionDescription'],
        'created_at' => Carbon::now(),
        'updated_at' => Carbon::now()
      ];

      DB::beginTransaction();
      Permission::where('id', $id)->update($permissionData);

      DB::commit();
      return [
        'status' => true,
        'id' => $id,
        'message' => 'success'
      ];
    } catch (\Exception $e) {
      DB::rollBack();
      return [
        'status' => false,
        'message' => $e->getMessage()
      ];
    }
  }

  public function processDeletePermission($id)
  {
    try {
      DB::beginTransaction();
      $permission = Permission::find($id);
      $permission->roles()->detach();
      $permission->delete();
      DB::commit();
      return [
        'status' => true,
        'message' => 'success'
      ];
    } catch (\Exception $e) {
      DB::rollBack();
      return [
        'status' => false,
        'message' => $e->getMessage()
      ];
    }
  }
}
