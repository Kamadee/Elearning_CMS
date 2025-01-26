<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CustomerController;
use Illuminate\Support\Facades\Auth;
// Route::get('/ho', function () {
//   return 8;
//   dd(666);
// });
Route::middleware('auth.jwt')->group(function () {
  Route::get('/user', function () {
      return Auth::guard('api')->user();
  });
});

Route::post('/customer/login', [CustomerController::class, 'postLogin']);
Route::post('/customer/register', [CustomerController::class, 'postRegister']);
Route::post('/customer/verify', [CustomerController::class, 'verifyEmail']);
