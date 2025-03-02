<?php

namespace App\Http\Middleware;

use App\Helpers\ResponseCode;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class JWTVerifyCustomer
{
  public function handle(Request $request, Closure $next)
  {
    if (!auth('customer')->check()) {
      return response()->json([
        'errorCode' => ResponseCode::$UNAUTHORIZED,
        'message' => __('error')[ResponseCode::$UNAUTHORIZED]
      ], Response::HTTP_UNAUTHORIZED);
    }

    if (auth('customer')->user()->status !== Config::get('constants.customer_status_enable')) {
      auth('customer')->logout();
      return response()->json([
        'errorCode' => ResponseCode::$UNAUTHORIZED,
        'message' => __('message.customer_not_enable')
      ], Response::HTTP_UNAUTHORIZED);
    }

    return $next($request);
  }
}
