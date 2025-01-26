<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Services\CustomerServices;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Helpers\Helper;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Redis;
use Illuminate\Validation\ValidationException;
use App\Mail\CustomerActiveMail;
use App\Services\CustomerService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class CustomerController extends Controller
{
  private $customerServices;

  public function __construct(CustomerServices $customerServices)
  {
    $this->customerServices = $customerServices;
  }

  public function postRegister(Request $request)
  {
    try {
      DB::beginTransaction();
      $request->validate([
        "first_name" => 'required|string|max:255',
        "last_name" => 'required|string|max:255',
        "email" => 'required|string|unique:customer,email',
        "password" => 'required|string|min:6',
      ]);
      $data = [
        "first_name" => $request->first_name,
        "last_name" => $request->last_name,
        "email" => $request->email,
        "password" => Hash::make($request->password),
        'status' => Config::get('constants.customer_status_disable'),
        "confirmation_code" => Str::random(6),
      ];
      $customner = Customer::create($data);
      $newCustomer = Customer::find($customner->id);
      // Mail::to($newCustomer->email)->send(new \App\Mail\CustomerActiveMail($newCustomer->confirmation_code));
      Mail::to($newCustomer->email)->send(new CustomerActiveMail($newCustomer->confirmation_code));
      DB::commit();
      return response()->json([
        'status' => 201,
        'message' => "Đăng ký thành công",
        'user' => $newCustomer,
      ]);
    } catch (ValidationException $e) {
      // Handle validation exceptions
      Helper::createLogError(__FILE__ . ':' .  __LINE__ . ' ' . $e);
      DB::rollBack();
      return response()->json([
        'status' => 422,
        'error' => 'Validation failed.',
        'errors' => $e->errors(),
      ], 422);
    } catch (\Exception $e) {
      Helper::createLogError(__FILE__ . ':' .  __LINE__ . ' ' . $e);
      DB::rollBack();
      return response()->json([
        'status' => 500,
        'error' => 'An error occurred while registering the customer.',
      ], 500);
    }
  }

  public function verifyEmail(Request $request)
  {
    try {
      DB::beginTransaction();
      $code = $request->code;
      $customer = $this->customerServices->verify($code);

      if (!$customer) {
        DB::rollBack();
        return response()->json([
          'status' => 404,
          'error' => 'Mã xác nhận không hợp lệ',
        ]);
      }

      if ($customer && $customer->status === Config::get('constants.customer_status_enable')) {
        DB::commit();
        return response()->json([
          'status' => 201,
          'message' => 'Xác thực email thành công',
          'user' => $customer,
        ]);
      }
      // Trường hợp khách hàng đã xác thực email trước đó
      DB::rollBack();
      return response()->json([
        'status' => 400,
        'error' => 'Email đã được xác thực trước đó.',
      ]);
    } catch (\Exception $e) {
      Helper::createLogError(__FILE__ . ':' .  __LINE__ . ' ' . $e);
      DB::rollBack();
      return response()->json([
        'status' => 500,
        'error' => 'An error occurred while registering the customer.',
      ], 500);
    }
  }

  public function postLogin(Request $request)
  {
    try {
      $request->validate([
        "email" => 'required|string',
        "password" => 'required|string|min:6',
      ]);
      $credentials = [
        "email" => $request->email,
        "password" => $request->password,
      ];
      if (!$token = Auth::guard('customer')->attempt($credentials)) {
        return response()->json([
          'status' => 401,
          'message' => "Email hoặc mật khẩu không chính xác!",
        ]);
      }
      $customer = Auth::guard('customer')->user();
      if ($customer->status !== Config::get('constants.customer_status_disable')) {
        Auth::logout();
        return response()->json([
          'status' => 403,
          'message' => "Tài khoản Email chưa được kích hoạt",
        ]);
      }
      return response()->json([
        'status' => 200,
        'message' => "Đăng nhập thành công",
        'user' => $customer,
        'token' => $token,
      ]);
    } catch (\Exception $e) {
      Helper::createLogError(__FILE__ . ':' .  __LINE__ . ' ' . $e);
      return response()->json([
        'status' => 500,
        'message' => 'Lỗi server, vui lòng thử lại sau!',
      ]);
    }
  }

  public function profile(Request $request)
  {
    try {
      $customer = auth('customer')->user();
      if ($customer) {
        return $this->successResponse($customer);
      }
      return $this->notFoundErrorResponse();
    } catch (\Exception $e) {
      Helper::createLogError(__FILE__ . ':' .  __LINE__ . ' ' . $e);
      return $this->internalServerErrorResponse();
    }
  }
}
