<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
  protected $table = 'carts';
  protected $fillable = [
    'customer_id',
    'course_id',
    'course_title',
    'quantity',
    'price',
  ];
  public $timestamps = true;

  public function customer()
  {
    return $this->belongsTo(Customer::class, 'customer_id');
  }
  public function course()
  {
    return $this->belongsTo(Course::class, 'course_id');
  }
}
