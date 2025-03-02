<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use App\Services\UserServices;
use Illuminate\Auth\Events\Validated;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
  private $userServices;

  public function __construct(UserServices $userServices)
  {
    $this->userServices = $userServices;
  }
  public function showLogin(Request $request)
  {
    return view('auth.login');
  }
  public function postLogin(Request $request)
  {
    $request->validate([
      'username' => 'required|max:255',
      'password' => 'required|max:255|min:6',
    ]);
    $credentials = [
      'username' => $request->username,
      'password' => $request->password,
      'status' => array_keys(Config::get('constants.user_status'))[1],
    ];

    // dd($credentials);
    if (Auth::attempt($credentials)) {

      return redirect('/home');
    }
    return redirect()->back()->with('error', 'Unauthenticated');
  }
  public function logout()
  {
    Session::flush();
    Auth::logout();
    return redirect()->route('auth.login');
  }
  public function detail()
  {
    $currentUser = Auth::user();
    $user = User::with('roles')->find($currentUser->id);
    $roleList = $this->userServices->getRoleList();
    return view('auth.detail', [
      'user' => $user,
      'roleList' => $roleList
    ]);
  }
  public function updateProfile(Request $request)
  {
    $data = $request->all();
    $validator = Validator::make($data, [
      'username' => 'required|max:255',
      Rule::unique('username')->ignore(Auth::user()->id),
      'password' => 'nullable|max:255|min:6'
    ]);
    if ($validator->fails()) {
      return redirect()->route('auth.detail')
        ->withErrors($validator)
        ->withInput();
    }
    $result = $this->userServices->processUpdateSelf($data);
    if ($result['status']) {
      return redirect()->route('auth.detail')->withSuccess(__(
        'admin.message.update_account_success',
        ['username' => $request->username]
      ));
    }
    return redirect()->route('auth.detail')->withErrors($result['message'])->withInput();
  }
}
