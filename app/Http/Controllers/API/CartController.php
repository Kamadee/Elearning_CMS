<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\CartServices;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Helpers\Helper;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CartController extends Controller
{
  private $cartServices;
  public function __construct(CartServices $cartServices)
  {
    $this->cartServices = $cartServices;
  }
  public function getCartContent(Request $request)
  {
    try {
      $content = $this->cartServices->getCartContent();
      return $this->successResponse(['content' => $content]);
    } catch (\Exception $e) {
      Helper::createLogError(__FILE__ . ':' .  __LINE__ . ' ' . $e);
      return $this->internalServerErrorResponse();
    }
  }
  public function addCartItem(Request $request)
  {
    DB::beginTransaction();
    try {
      $request->validate([
        "course_id" => 'required|integer',
        "quantity" => 'required|integer',
      ]);
      $this->cartServices->addCartItem([
        'course_id' => $request->course_id,
        'quantity' => $request->quantity
      ]);
      DB::commit();
      return $this->createdSuccessResponse();
    } catch (\Exception $e) {
      dd($e);
      Helper::createLogError(__FILE__ . ':' .  __LINE__ . ' ' . $e);
      return $this->internalServerErrorResponse();
    }
  }

  public function updateCartItem($id, Request $request)
  {
    DB::beginTransaction();
    try {
      $request->validate([
        "quantity" => 'required|integer|min:1',
      ]);
      $this->cartServices->updateCartItem($id, [
        'quantity' => $request->quantity
      ]);
      DB::commit();
      return $this->successResponse();
    } catch (\Exception $e) {
      Helper::createLogError(__FILE__ . ':' .  __LINE__ . ' ' . $e);
      return $this->internalServerErrorResponse();
    }
  }

  public function deleteCartItem($id)
  {
    DB::beginTransaction();
    try {
      $this->cartServices->deleteCartItem($id);
      DB::commit();
      return $this->deletedSuccessResponse();
    } catch (NotFoundHttpException $e) {
      Helper::createLogError(__FILE__ . ':' .  __LINE__ . ' ' . $e);
      DB::rollBack();
      return $this->notFoundErrorResponse();
    } catch (\Exception $e) {
      Helper::createLogError(__FILE__ . ':' .  __LINE__ . ' ' . $e);
      return $this->internalServerErrorResponse();
    }
  }

  public function destroyCart(Request $request)
  {
    DB::beginTransaction();
    try {
      $this->cartServices->destroyCart();
      DB::commit();
      return $this->successResponse();
    } catch (\Exception $e) {
      // Handle any other exceptions
      Helper::createLogError(__FILE__ . ':' .  __LINE__ . ' ' . $e);
      DB::rollBack();
      return $this->internalServerErrorResponse();
    }
  }
}
