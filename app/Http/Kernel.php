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
    ];
}
