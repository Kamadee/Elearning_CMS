<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\CustomerController;
use App\Http\Controllers\API\RoomController;
use App\Http\Controllers\API\PostController;
use App\Http\Controllers\API\CartController;
use App\Http\Controllers\API\CourseController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\API\PaymentController;
use App\Http\Controllers\API\VideoController;
use App\Http\Middleware\JWTVerifyCustomer;

Route::get('/ho', function () {
  return 8;
});
// Route::middleware('auth.jwt')->group(function () {
//   Route::get('/user', function () {
//     return Auth::guard('api')->user();
//   });
// });
// Route::middleware('auth:api')->get('/user', function (Request $request) {
//   return $request->user();
// });

Route::post('/customer/login', [CustomerController::class, 'postLogin']); //ok
Route::post('/customer/register', [CustomerController::class, 'postRegister']); //ok
Route::post('/customer/verify', [CustomerController::class, 'verifyEmail']); //ok
Route::post('/customer/forgot-password', [CustomerController::class, 'sendResetLinkEmail']);
Route::post('/customer/reset-password', [CustomerController::class, 'reset'])->name('password.reset');

// AUTHENTICATE FOR CUSTOMER 
Route::group(['middleware' => [JWTVerifyCustomer::class]], function () {
  // Cấu hình jwt.verify-customer trong Kernel
  Route::group(['prefix' => 'customer'], function () {
    Route::patch('/update-profile', [CustomerController::class, 'updateProfile']); //ok
    Route::get('/profile', [CustomerController::class, 'profile']); //ok
    Route::patch('/change-password', [CustomerController::class, 'changePassword']); //ok
    Route::post('/logout', [AuthController::class, 'logout']); //ok
  });
});

// ORDER FOR CUSTOMER
Route::group(['middleware' => [JWTVerifyCustomer::class]], function () {
  Route::group(['prefix' => 'customer'], function () {
    Route::get('/orders', [CustomerController::class, 'getOrders']);
    Route::get('/orders/{id}', [CustomerController::class, 'getOrderById']);
  });
});

//POST FOR CUSTOMER
Route::get('/post/list', [PostController::class, 'getPostList']); // ok 
Route::get('/post/{id}', [PostController::class, 'getPostDetail']); // ok
// Route::get('/tag/popular', [TagController::class, 'getPopularTagList']);

// CART - Ok
Route::group(['middleware' => [JWTVerifyCustomer::class]], function () {
  Route::group(['prefix' => 'cart'], function () {
    Route::get('/content', [CartController::class, 'getCartContent']); // ok
    Route::post('/add', [CartController::class, 'addCartItem']); // ok
    Route::post('/update/{id}', [CartController::class, 'updateCartItem']); // ok "nhưng test course_id = 1 thì lỗi
    Route::delete('/delete/{id}', [CartController::class, 'deleteCartItem']); // ok
    Route::post('/destroy', [CartController::class, 'destroyCart']);
  });
});

// COURSE FOR CUSTOMER
Route::group(['prefix' => 'course'], function () {
  Route::get('/list', [CourseController::class, 'getCourseList']);
  Route::get('/detail/{id}', [CourseController::class, 'getCourseDetail']);
  Route::get('/top', [CourseController::class, 'getCourseTop']); //ok
});

// PAYMENT VNPAY
Route::group(['middleware' => [JWTVerifyCustomer::class]], function () {
  Route::group(['prefix' => 'payment'], function () {
    Route::post('/create', [PaymentController::class, 'createPayment']); // đợi sơn
    Route::get('/result', [PaymentController::class, 'resultPayment']);
  });
});

// API CALLBACK FOR VNPAY (IPN URL)
Route::group(['prefix' => 'payment'], function () {
  Route::get('/response', [PaymentController::class, 'responsePayment']);
});

Route::group(['prefix' => 'video', 'as' => 'video'], function () {
  Route::get('/vimeo/{id}', [VideoController::class, 'getDetailVimeo']); // chưa làm
});
