<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\User;
use App\Models\Tag;
use App\Models\Post;
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
          $action .= '<button data-id="' . $row->id . '" data-name="' . $row->username . '" class="btn-delete-admin btn btn-danger btn-sm">' . __('admin.delete_admin') . '</button>';
        }
        return $action;
      })
      ->rawColumns(['role_name', 'role_description', 'action'])
      ->make(true);
  }

  public function processDeleteUser($id)
  {
    try {
      DB::beginTransaction();
      $user = User::find($id);
      $user->roles()->detach();
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
}
