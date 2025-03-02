<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\PaymentServices;
use Illuminate\Http\Request;
use App\Helpers\Helper;
use Illuminate\Support\Facades\DB;
use App\Helpers\ResponseCode;
use Illuminate\Http\Response;

class PaymentController extends Controller
{
  private $paymentServices;
  public function __construct(PaymentServices $paymentServices)
  {
    $this->paymentServices = $paymentServices;
  }

  public function createPayment(Request $request)
  {
    DB::beginTransaction();
    try {
      $data = $this->paymentServices->createPayment($request);
      DB::commit();
      return $this->successResponse($data);
    } catch (\Exception $e) {
      Helper::createLogError(__FILE__ . ':' .  __LINE__ . ' ' . $e);
      DB::rollBack();
      return $this->internalServerErrorResponse();
    }
  }

  public function resultPayment(Request $request)
  {
    try {
      $data = $this->paymentServices->resultPayment($request);
      return $this->successResponse($data);
    } catch (\Exception $e) {
      Helper::createLogError(__FILE__ . ':' .  __LINE__ . ' ' . $e);
      DB::rollBack();
      return $this->customErrorResponse(
        ResponseCode::$INTERNAL_ERROR,
        $e->getMessage(),
        Response::HTTP_INTERNAL_SERVER_ERROR
      );
    }
  }

  public function responsePayment(Request $request)
  {
    DB::beginTransaction();
    try {
      $data = $this->paymentServices->responsePayment($request);
      DB::commit();
      return response()->json($data);
    } catch (\Exception $e) {
      Helper::createLogError(__FILE__ . ':' .  __LINE__ . ' ' . $e);
      DB::rollBack();
      return response()->json([
        'RspCode' => '99',
        'Message' => 'Unknow error'
      ]);
    }
  }
}
