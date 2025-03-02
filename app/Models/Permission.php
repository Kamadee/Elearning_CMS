<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Permission extends Model
{
  use SoftDeletes;

  protected $table = 'permissions';

  protected $fillable = ['permission_name', 'guard_name', 'description'];

  protected $hidden = ['deleted_at', 'pivot'];

  public function roles()
  {
    return $this->belongsToMany(Role::class, 'roles_permissions')->withTimestamps();
  }
}
