<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use PhpParser\Node\Expr\BinaryOp\Pow;

class Post extends Model
{
  protected $table = 'posts';

  public $timestamps = true;

  public function postCategories()
  {
    return $this->belongsToMany(PostCategory::class, 'post_category_pivot');
  }
  public function postTags()
  {
    return $this->belongsToMany(Tag::class, 'post_tags');
  }
  public static function getPostRelationShipById($id)
  {
    $post = Post::with(['postCategories', 'postTags'])->find($id);
    return $post;
  }
}
