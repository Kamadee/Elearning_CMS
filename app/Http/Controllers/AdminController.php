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
    $roleList = Role::all();
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
    $validator = Validator::make($request->all(), [
      'title' => 'required|max:255',
      'description' => 'max:1000',
      'content' => 'required',
      'input-pd' => 'required',
      'status' => 'required'
    ]);
    if ($validator->fails()) {
      return redirect()->route('posts.create')
        ->withErrors($validator)
        ->withInput();;
    }
    $result = $this->postServices->processCreatePost($request->all());
    if ($result['status']) {
      $postId = $result['id'];
      return redirect()->route('posts.detail', ['id' => $postId])
        ->withSuccess(__('post.message.create_post_success'));
    }
    return redirect()->route('posts.create')
      ->withErrors($result['message'])
      ->withInput();
  }

  public function updateAccount(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'id' => 'exists:posts,id',
      'title' => 'required|max:255',
      'description' => 'max:1000',
      'content' => 'required',
      'status' => 'required'
    ]);
    if ($validator->fails()) {
      return redirect()->route('posts.detail', ['id' => $request->id])
        ->withErrors($validator)
        ->withInput();;
    }
    $result = $this->postServices->processUpdatePost($request->id, $request->all());
    // dd($result);
    if ($result['status']) {
      return redirect()->route('posts.detail', ['id' => $request->id])
        ->withSuccess(__('post.message.update_post_success', ['id' => $request->id]));
    }
    return redirect()->route('posts.detail', ['id' => $request->id])
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
}
