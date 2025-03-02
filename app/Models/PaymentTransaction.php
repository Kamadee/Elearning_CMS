<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Helpers\Helper;

class PaymentTransaction extends Model
{
  protected $table = 'payment_transactions';
  protected $fillable = [
    'code',
    'order_id',
    'customer_id',
    'amount',
    'payment_method',
    'status',
  ];
  public $timestamps = true;

  public function order()
  {
    $this->belongsTo(Order::class, 'order_id');
  }
  public function customer()
  {
    $this->belongsTo(Customer::class, 'customer_id');
  }
  public static function generateCode($prefix, $index = null, $field = null)
  {
    $tableName = 'payment_transactions';
    DB::select("SET SESSION INFORMATION_SCHEMA_STATS_EXPIRY = 0");
    $statement = DB::select("SHOW TABLE STATUS LIKE '$tableName'");
    $index = $statement[0]->Auto_increment;
    $index = $index ? $index : 1;
    $code =  Helper::generateCodeUlid($prefix, $index);
    $field = $field ?? 'code';
    if (self::codeExists($code, $field)) {
      return self::generateCode($prefix, $index, $field);
    }

    return $code;
  }

  public static function codeExists($code, $field = null)
  {
    return Order::where($field, $code)->exists();
  }
}
