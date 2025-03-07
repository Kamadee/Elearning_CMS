<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Course extends Model
{
  use SoftDeletes;

  protected $dates = ['deleted_at'];
  protected $table = 'courses';

  public $timestamps = true;

  public function courseTags()
  {
    return $this->belongsToMany(Tag::class, 'course_tags');
  }
  public function courseCategories()
  {
    return $this->belongsToMany(PostCategory::class, 'course_category_pivot');
  }
  public function videos()
  {
    return $this->hasMany(CourseVideo::class);
  }
  public function orders()
  {
    return $this->belongsToMany(Order::class, 'order_items', 'course_id', 'order_id');
  }
  public static function getCourseRelationShipById($id)
  {
    $course = Course::with('courseCategories', 'courseTags', 'videos')->find($id);
    return $course;
  }
}
