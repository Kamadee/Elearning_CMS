<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HotContent extends Model
{
  protected $table = 'hot_contents';

  public $timestamps = true;

  public function course()
  {
    return $this->belongsTo(Course::class, 'content_id');
  }
}
