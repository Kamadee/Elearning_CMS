<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;
use App\Http\Middleware\JWTVerifyCustomer;

class Kernel extends HttpKernel
{
  /**
   * The application's global HTTP middleware stack.
   *
   * These middleware are run during every request to your application.
   *
   * @var array<int, class-string|string>
   */
  protected $middlewareGroups = [
    'api' => [
      'throttle:api',
      \Illuminate\Routing\Middleware\SubstituteBindings::class,
      'auth.jwt', // Thêm middleware auth.jwt cho API
    ],
    \Illuminate\Http\Middleware\HandleCors::class,
  ];
  protected $routeMiddleware = [
    'auth' => \App\Http\Middleware\Authenticate::class,
    'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
    'bindings' => \Illuminate\Routing\Middleware\SubstituteBindings::class,
    'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
    'can' => \Illuminate\Auth\Middleware\Authorize::class,
    'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
    'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,
    'signed' => \Illuminate\Routing\Middleware\ValidateSignature::class,
    'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
    'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
    // 'sso-verified' => \App\Http\Middleware\SSOMiddleware::class,
    // 'permission-check' => \App\Http\Middleware\PermissionMiddleware::class,
    // 'custom.logs' => \App\Http\Middleware\CustomLogs::class,
  ];

  // protected $routeMiddleware = [
  //   'jwt.verify-customer' => \App\Http\Middleware\JWTVerifyCustomer::class,
  // ];
}
