<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Model;
use App\Permissions\HasPermissionsTrait;

class User extends Authenticatable
{
  use HasPermissionsTrait;

  protected $table = 'users';

  public $timestamps = true;

  protected $hidden = [
    'password'
  ];

  public function roles()
  {
    return $this->belongsToMany(Role::class, 'users_roles')->withTimestamps();
  }

  public static function getAllAdmin()
  {
    return User::all();
  }
}
