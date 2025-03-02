<?php

namespace App\Http\Middleware;

use Illuminate\Support\Facades\Redirect;
use Closure;
use Illuminate\Http\Request;
use App\Helpers\Helper;

class PermissionMiddleware
{
  public function handle($request, Closure $next, $permissionGuardName)
  {
    if (!Helper::checkPermission($permissionGuardName)) {
      return redirect('/forbidden');
    }

    return $next($request);
  }
}
