<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
  protected $table = 'orders';

  public $timestamps = true;

  public function orderItems()
  {
    return $this->hasMany(OrderItem::class);
  }
  public function courses()
  {
    return $this->belongsToMany(Course::class, 'order_items', 'order_id', 'course_id');
  }
  public function paymentTransaction()
  {
    return $this->hasOne(PaymentTransaction::class, 'order_id');
  }
}
