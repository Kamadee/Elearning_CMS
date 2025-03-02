<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
  protected $table = 'tags';

  public $timestamps = true;

  public function posts()
  {
    return $this->belongsToMany(Post::class, 'post_tags');
  }

  public static function getTagList()
  {
    return Tag::all();
  }
}
