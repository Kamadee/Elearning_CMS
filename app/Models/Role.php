<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Role extends Model
{
  use SoftDeletes;

  protected $table = 'roles';

  protected $fillable = ['role_name', 'description'];

  protected $hidden = ['deleted_at', 'pivot'];

  public function permissions()
  {
    return $this->belongsToMany(Permission::class, 'roles_permissions')->withTimestamps();
  }

  public function users()
  {
    return $this->belongsToMany(User::class, 'users_roles')->withTimestamps();
  }
}
