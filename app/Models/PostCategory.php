<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostCategory extends Model
{
  protected $table = 'post_categories';

  public $timestamps = true;

  public function posts()
  {
    return $this->belongsToMany(Post::class, 'post_category_pivot');
  }

  public static function getPostCategoryList()
  {
    return PostCategory::all();
  }
}
