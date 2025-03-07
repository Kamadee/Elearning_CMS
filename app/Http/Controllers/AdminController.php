<?php

namespace App\Http\Controllers;

use App\Services\UserServices;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\User;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
  private $userServices;

  public function __construct(UserServices $userServices)
  {
    $this->userServices = $userServices;
  }

  public function list(Request $request)
  {
    return view('admin/index');
  }

  public function anyData()
  {
    $data = $this->userServices->getAdmins();
    $datatableFormat = $this->userServices->formatAdminDatatables($data);
    return $datatableFormat;
  }

  public function adminDetail(Request $request)
  {
    $user = User::with(['roles'])->find($request->id);
    $roleList = Role::with(['permissions'])->get();
    return view('admin.detail', [
      'user' => $user,
      'roleList' => $roleList,
    ]);
  }

  public function createAccount()
  {
    $roleList = Role::all();
    return view(
      'admin.create',
      [
        'roleList' => $roleList,
      ]
    );
  }

  public function storeNewAccount(Request $request)
  {
    $data = $request->all();
    $validator = Validator::make($data, [
      'username' => [
        'required',
        'max:255',
        Rule::unique('users'),
      ],
      'password' => 'required|min:6|max:255'
    ]);

    if ($validator->fails()) {
      return redirect()->route('admin.createAccount')
        ->withErrors($validator)
        ->withInput();
    }

    $result = $this->userServices->processCreateAccount($data);
    if ($result['status']) {
      return redirect()->route('admin.detail', ['id' => $result['id']])->withSuccess(__('admin.message.create_account_success', ['username' => $request->username]));
    }
    return redirect()->route('admin.createAccount')
      ->withErrors($result['message'])
      ->withInput();
  }

  public function updateAccount(Request $request)
  {
    $data = $request->all();
    $validator = Validator::make($data, [
      'id' => 'exists:users,id',
      'username' => [
        'required',
        'max:255',
        Rule::unique('users')->ignore($request->id),
      ],
      'password' => 'nullable|min:6|max:255'
    ]);

    if ($validator->fails()) {
      return redirect()->route('admin.detail', ['id' => $request->id])
        ->withErrors($validator)
        ->withInput();
    }
    $result = $this->userServices->processUpdateAccount($request->id, $data);
    if ($result['status']) {
      return redirect()->route('admin.detail', ['id' => $request->id])->withSuccess(__('admin.message.update_account_success', ['username' => $request->username]));
    }
    return redirect()->route('admin.detail', ['id' => $request->id])
      ->withErrors($result['message'])
      ->withInput();
  }

  public function deleteUser(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'id' => 'exists:users,id',
    ]);
    if ($validator->fails()) {
      return [
        'status' => false,
        'message' => __('post.message.post_not_found')
      ];
    }
    $userId = $request->id;
    return $this->userServices->processDeleteUser($userId);
  }

  public function getRoleList(Request $request)
  {
    return view('role.index');
  }

  public function roleAnyData(Request $request)
  {
    $data = $this->userServices->getRoleList();
    $datatableFormat = $this->userServices->formatRoleDatatables($data);
    return $datatableFormat;
  }

  public function create(Request $request)
  {
    $permissionGroupBy = $this->userServices->getGroupByPermission();
    return view('role.create', ['permissionList' => $permissionGroupBy]);
  }

  public function createRole(Request $request)
  {
    $data = $request->all();
    $validator = Validator::make($data, [
      'roleName' => 'required|max:255',
      'roleDescription' => 'required|max:255'
    ]);

    if ($validator->fails()) {
      return redirect()->route('role.create')
        ->withErrors($validator)
        ->withInput();
    }
    $result = $this->userServices->processCreateRole($data);
    if ($result['status']) {
      return redirect()->route('role.detail', ['id' => $result['id']])->withSuccess(__('role.message.create_role_success'));
    }
    return redirect()->route('role.create')
      ->withErrors($result['message'])
      ->withInput();
  }

  public function roleDetail(Request $request)
  {
    $role = $this->userServices->getRoleDetail($request->id);
    $permissionGroupBy = $this->userServices->getGroupByPermission();
    return view('role.detail', ['role' => $role, 'permissionList' => $permissionGroupBy]);
  }

  public function updateRole(Request $request)
  {
    $data = $request->all();
    $validator = Validator::make($data, [
      'id' => 'exists:roles,id',
      'roleName' => 'required|max:255',
      'roleDescription' => 'required|max:255'
    ]);

    if ($validator->fails()) {
      return redirect()->route('role.detail', ['id' => $request->id])
        ->withErrors($validator)
        ->withInput();
    }

    $result = $this->userServices->processUpdateRole($request->id, $data);
    if ($result['status']) {
      return redirect()->route('role.detail', ['id' => $request->id])->withSuccess(__('role.message.update_role_success', ['roleName' => $request->roleName]));
    }
    return redirect()->route('role.detail', ['id' => $request->id])
      ->withErrors($result['message'])
      ->withInput();
  }

  public function deleteRole(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'id' => 'exists:roles,id',
    ]);

    if ($validator->fails()) {
      return ['status' => false, 'message' => __('role.message.role_not_found')];
    }
    $roleId = $request->id;
    return $this->userServices->processDeleteRole($roleId);
  }
}
