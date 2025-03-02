<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
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
use App\Notifications\ResetPasswordNotification;
use App\Notifications\CustomerActiveNotification;
use App\Services\CustomerService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Password;
use Tymon\JWTAuth\Claims\Custom;
use Tymon\JWTAuth\Facades\JWTAuth;

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
        "email" => 'required|string|unique:customers,email',
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
      $newCustomer->notify(new CustomerActiveNotification($newCustomer->confirmation_code));
      DB::commit();
      return response()->json([
        'status' => 201,
        'message' => "Đăng ký thành công",
        'customer' => $newCustomer,
        'code' => $newCustomer->confirmation_code
      ], 201);
    } catch (ValidationException $e) {
      dd($e);
      // Handle validation exceptions
      Helper::createLogError(__FILE__ . ':' .  __LINE__ . ' ' . $e);
      DB::rollBack();
      return response()->json([
        'status' => 422,
        'error' => 'Validation failed.',
        'errors' => $e->errors(),
      ], 422);
    } catch (\Exception $e) {
      dd($e);
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
      $code = $request->code;
      $this->customerServices->verify($code);
      DB::commit();
      return $this->successResponse();
      // DB::beginTransaction();
      // $code = $request->code;
      // $customer = $this->customerServices->verify($code);

      // if (!$customer) {
      //   DB::rollBack();
      //   return response()->json([
      //     'status' => 404,
      //     'error' => 'Mã xác nhận không hợp lệ',
      //   ], 404);
      // }

      // if ($customer && $customer->status === Config::get('constants.customer_status_enable')) {
      //   DB::commit();
      //   return response()->json([
      //     'status' => 201,
      //     'message' => 'Xác thực email thành công',
      //     'customer' => $customer,
      //   ], 201);
      // }
      // // Trường hợp khách hàng đã xác thực email trước đó
      // DB::rollBack();
      // return response()->json([
      //   'status' => 400,
      //   'error' => 'Email đã được xác thực trước đó.',
      // ], 400);
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
        'email' => 'required|max:255|email',
        'password' => 'required|min:6|max:255',
      ]);
      $token = Auth::guard('customer')->attempt([
        'email' => $request->email,
        'password' => $request->password,
        'status' => Config::get('constants.customer_status_enable'),
      ]);
      if ($token) {
        $customer = Auth::guard('customer')->user();
        return response()->json([
          'status' => 200,
          'access_token' => $token,
          'user' => $customer,
          'token_type' => 'Bearer',
        ]);
      }

      return response()->json([
        'status' => 401,
        'error' => 'Invalid login credentials or token.',
      ], 401);
    } catch (\Exception $e) {
      dd($e);
      Helper::createLogError(__FILE__ . ':' .  __LINE__ . ' ' . $e);
      return $this->internalServerErrorResponse();
    }
  }

  public function logout(Request $request)
  {
    JWTAuth::invalidate(JWTAuth::getToken()); // Hủy token hiện tại

    return response()->json([
      'message' => 'Logged out successfully'
    ], 200);
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

  public function changePassword(Request $request)
  {
    DB::beginTransaction();
    try {
      $request->validate([
        'current_password' => 'required|string|min:6',
        'new_password' => 'required|string|min:6',
      ]);
      $customer = auth('customer')->user();
      if ($customer) {
        if (Hash::check($request->new_password, $customer->password)) {
          throw ValidationException::withMessages([
            'message' => 'The new password and old password must not be the same'
          ]);
        }
        $customer->password = Hash::make($request->new_password);
        $customer->save(); // Đang sửa lỗi này dở //////////
        Auth::guard('customer')->logout();
        DB::commit();
      }
      return response()->json([
        'status' => 201,
        'message' => "Thay đổi mật khẩu thành công",
        'customer' => $customer,
      ], 201);
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

  public function updateProfile(Request $request)
  {
    DB::beginTransaction();
    try {
      $request->validate([
        'first_name' => 'string|max:255',
        'last_name' => 'string|max:255',
        'phone' => 'string|max:15',
      ]);

      $customer = auth('customer')->user();
      $customer->first_name = $request->first_name ? $request->first_name : $customer->first_name;
      $customer->last_name = $request->last_name ? $request->last_name : $customer->last_name;
      $customer->email = $request->email ? $request->email : $customer->email;
      $customer->phone = $request->phone ? $request->phone : $customer->phone;
      $customer->save();

      DB::commit();
      return response()->json([
        'status' => 201,
        'message' => 'User successfully update',
        'user' => $customer
      ], 201);
    } catch (ValidationException $e) {
      Helper::createLogError(__FILE__ . ':' .  __LINE__ . ' ' . $e);
      DB::rollBack();
      return response()->json([
        'status' => 422,
        'error' => 'Validation failed.',
        'errors' => $e->errors(),
      ], 422);
    } catch (\Exception $e) {
      // Handle any other exceptions
      Helper::createLogError(__FILE__ . ':' .  __LINE__ . ' ' . $e);
      DB::rollBack();
      return response()->json([
        'status' => 500,
        'error' => 'An error occurred while update the customer.',
      ], 500);
    }
  }
  // Tạm thời phong ấn 2 chức năng này "Gửi confirm_code đến email trước khi đặt lại mk"
  // public function sendResetLinkEmail(Request $request)
  // {
  //   try {
  //     $request->validate([
  //       "email" => 'required|string|exists:customer,email',
  //     ]);
  //     $status = Password::broker('customers')->sendResetLink($request->only('email'));

  //     if ($status == Password::RESET_LINK_SENT) {
  //       return response()->json(['message' => 'Liên kết đặt lại mật khẩu đã được gửi đến email của bạn']);
  //     }

  //     return response()->json(['error' => 'Không thể gửi email đặt lại mật khẩu.'], 500);
  //   } catch (ValidationException $e) {
  //     Helper::createLogError(__FILE__ . ':' .  __LINE__ . ' ' . $e);
  //     DB::rollBack();
  //     return response()->json([
  //       'status' => 422,
  //       'error' => 'Validation failed.',
  //       'errors' => $e->errors(),
  //     ], 422);
  //   } catch (\Exception $e) {
  //     Helper::createLogError(__FILE__ . ':' .  __LINE__ . ' ' . $e);
  //     DB::rollBack();
  //     return response()->json([
  //       'status' => 500,
  //       'error' => 'An error occurred while registering the customer.',
  //     ], 500);
  //   }
  // }

  // public function reset(Request $request)
  // {
  //   try {
  //     $request->validate([
  //       'email' => 'required|email|exists:customers,email',
  //       'password' => 'required|string|min:6|confirmed',
  //       'token' => 'required|string',
  //     ]);

  //     $status = Password::broker('customers')->reset(
  //       $request->only('email', 'password', 'password_confirmation', 'token'),
  //       function ($customer, $password) {
  //         $customer->password = Hash::make($password);
  //         $customer->save();
  //       }
  //     );

  //     if ($status == Password::PASSWORD_RESET) {
  //       return response()->json([
  //         'message' => 'Mật khẩu đã được đặt lại thành công'
  //       ]);
  //     }

  //     return response()->json([
  //       'message' => 'Đặt lại mật khẩu thất bại.'
  //     ], 500);
  //   } catch (ValidationException $e) {
  //     Helper::createLogError(__FILE__ . ':' .  __LINE__ . ' ' . $e);
  //     DB::rollBack();
  //     return response()->json([
  //       'status' => 422,
  //       'error' => 'Validation failed.',
  //       'errors' => $e->errors(),
  //     ], 422);
  //   } catch (\Exception $e) {
  //     Helper::createLogError(__FILE__ . ':' .  __LINE__ . ' ' . $e);
  //     DB::rollBack();
  //     return response()->json([
  //       'status' => 500,
  //       'error' => 'An error occurred while registering the customer.',
  //     ], 500);
  //   }
  // }

  public function getOrders(Request $request)
  {
    try {
      $data = $this->customerServices->getOrders($request);
      return $this->successResponse($data);
    } catch (\Exception $e) {
      Helper::createLogError(__FILE__ . ':' .  __LINE__ . ' ' . $e);
    }
  }

  public function getOrderById(Request $request)
  {
    try {
      $data = $this->customerServices->getOrderById($request->id);
      return $this->successResponse($data);
    } catch (\Exception $e) {
      Helper::createLogError(__FILE__ . ':' .  __LINE__ . ' ' . $e);
    }
  }
}
