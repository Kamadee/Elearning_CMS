<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Tag;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Cart;
use Illuminate\Http\Request;
use App\Helpers\Helper;
use App\Models\Course;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CartServices
{
  public function getCartContent()
  {
    $queries = Cart::with(['course'])
      ->select(
        'carts.id as id',
        'carts.customer_id as customer_id',
        'carts.course_title as course_title',
        'carts.quantity as quantity',
        'carts.price as price',
        DB::raw('(CASE WHEN sale_off_price IS NULL THEN original_price ELSE sale_off_price END) as price')
      )
      ->join('courses', 'courses.id', '=', 'carts.course_id')
      ->where('customer_id', auth('customer')->user()->id)
      ->get();
    return $queries;
  }

  public function addCartItem($data)
  {
    $cource = Course::find($data['course_id']);
    if (!$cource || !$data['quantity']) {
      return true;
    }
    $existCourseInCart = Cart::where('customer_id', auth('customer')->user()->id)
      ->where('course_id', $data['course_id'])
      ->first();
    if ($existCourseInCart) {
      // UPDATE +QUANTITY
      return $existCourseInCart->update([
        'quantity' => $existCourseInCart->quantity + $data['quantity']
      ]);
    } else {
      // CREATE NEW
      return Cart::create([
        'customer_id' => auth('customer')->user()->id,
        'course_id' => $data['course_id'],
        'course_title' => $cource->title,
        'quantity' => $data['quantity'],
        'price' => $cource->sale_off_price ? $cource->sale_off_price : $cource->original_price,
      ]);
    }
  }

  public function updateCartItem($id, $data)
  {

    $cartExist = Cart::where([
      'id' => $id,
      'customer_id' => auth('customer')->user()->id,
    ])->first();
    if ($cartExist) {
      return $cartExist->update([
        'quantity' => $data['quantity']
      ]);
    } else {
      throw new NotFoundHttpException(__('message.not_exist_cart_item'));
    };
  }

  public function deleteCartItem($id)
  {
    $cart = Cart::where('customer_id', auth('customer')->user()->id)
      ->where('id', $id)
      ->first();
    if ($cart) {
      return $cart->delete();
    } else {
      throw new NotFoundHttpException(__('message.not_exist_cart_item'));
    }
  }

  public function destroyCart()
  {
    return Cart::where('customer_id', auth('customer')->user()->id)->delete();
  }
}
