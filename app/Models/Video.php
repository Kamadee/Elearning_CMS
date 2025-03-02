<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Video extends Model
{
  protected $table = 'videos';

  public $timestamps = true;

  public function videoCategories()
  {
    return $this->belongsToMany(PostCategory::class, 'video_category_pivot');
  }

  public static function getTagList()
  {
    return Tag::all();
  }
}
