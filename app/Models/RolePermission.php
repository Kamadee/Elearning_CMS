<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RolePermission extends Model
{
  use SoftDeletes;

  protected $table = 'roles_permissions';

  protected $fillable = ['role_id', 'permission_id', 'created_at', 'updated_at', 'deleted_at'];

  protected $hidden = ['deleted_at', 'pivot'];
}
