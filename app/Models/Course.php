<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
  protected $table = 'courses';

  public $timestamps = true;

  public function courseTags()
  {
    return $this->belongsToMany(Tag::class, 'course_tags');
  }
  public function courseCategories()
  {
    return $this->belongsToMany(CourseCategoryPivot::class, 'course_category_pivot');
  }
  public function videos()
  {
    return $this->hasMany(CourseVideo::class);
  }
  public function orders()
  {
    return $this->belongsToMany(Order::class, 'order_items', 'course_id', 'order_id');
  }
}
