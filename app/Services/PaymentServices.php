<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Cart;
use App\Models\PaymentTransaction;
use App\Helpers\Helper;
use App\Models\OrderItem;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\HttpException;

class PaymentServices
{
  public function createPayment(Request $request)
  {
    $cartContent = Cart::select(
      'carts.id as id',
      'carts.customer_id as customer_id',
      'carts.course_id as course_id',
      'carts.quantity as quantity',
      'courses.title as course_title',
      DB::raw('(CASE WHEN sale_off_price IS NULL THEN original_price ELSE sale_off_price END) as price')
    )
      ->join('courses', 'courses.id', '=', 'carts.course_id')
      ->where('customer_id', auth('customer')->user()->id)
      ->whereIn('carts.id', $request->cart_id)
      ->get()
      ->toArray();

    if (count($cartContent)) {
      $amount = array_reduce(
        $cartContent,
        function ($amount, $item) {
          $amount += $item['price'] * $item['quantity'];
          return $amount;
        },
        0
      );
      // CREATE PROCESSING ORDER
      $order = Order::create([
        'code' => Order::generateCode('OD-', null, 'code'),
        'amount' => (int)$amount,
        'customer_id' => auth('customer')->user()->id,
        'payment_method' => Config::get('constants.payment_method.vnpay'),
        'payment_time' => null,
        'status' => Config::get('constants.order_status.processing'),
      ]);
      $orderItems = [];
      foreach ($cartContent as $item) {
        $orderItems[] = [
          'order_id' => $order->id,
          'course_id' => $item['course_id'],
          'course_title' => $item['course_title'],
          'quantity' => $item['quantity'],
          'price' => $item['price'],
          'created_at' => Carbon::now(),
          'updated_at' => Carbon::now(),
        ];
      }
      // CREATE ORDER ITEM
      OrderItem::insert($orderItems);
      // DESTROY CART
      Cart::where('customer_id', auth('customer')->user()->id)->delete();
      // CREATE PAYMENT TRANSACTION
      PaymentTransaction::create([
        'code' => PaymentTransaction::generateCode('PT-', null, 'code'),
        'order_id' => $order->id,
        'customer_id' => auth('customer')->user()->id,
        'amount' => $order->amount,
        'payment_method' => $order->payment_method,
        'status' => Config::get('constants.payment_transaction_status.waiting_confirm'),
      ]);
      // CREATE URL PAYMENT VNPAY
      $vnp_Url = Config::get('constants.vnpay_payment_url');
      $vnp_Returnurl = Config::get('constants.vnpay_payment_academy_return_url');
      $vnp_TmnCode = Config::get('constants.vnpay_payment_tmncode');
      $vnp_HashSecret = Config::get('constants.vnpay_payment_hashsecret');

      $vnp_TxnRef = $order->code; // ORDER CODE
      $vnp_OrderInfo = 'Thanh toan don hang ' . $order->code . ' thoi gian: ' . Carbon::now()->format('Y-m-d H:i:s');
      $vnp_OrderType = Config::get('constants.vnpay_order_type');
      $vnp_Amount = $order->amount * 100;
      $vnp_Locale = 'vn';
      $vnp_IpAddr = $request->ip();
      //Add Params of 2.0.1 Version
      $vnp_ExpireDate = Carbon::now()->addMinutes(Config::get('constants.vnpay_lifetime'))->format('YmdHis');
      $inputData = array(
        'vnp_Version' => '2.1.0',
        'vnp_TmnCode' => $vnp_TmnCode,
        'vnp_Amount' => $vnp_Amount,
        'vnp_Command' => 'pay',
        'vnp_CreateDate' => date('YmdHis'),
        'vnp_CurrCode' => 'VND',
        'vnp_IpAddr' => $vnp_IpAddr,
        'vnp_Locale' => $vnp_Locale,
        'vnp_OrderInfo' => $vnp_OrderInfo,
        'vnp_OrderType' => $vnp_OrderType,
        'vnp_ReturnUrl' => $vnp_Returnurl,
        'vnp_TxnRef' => $vnp_TxnRef,
        'vnp_ExpireDate' => $vnp_ExpireDate,
      );

      // LOG DATA SEND TO VNPAY
      Helper::createLogInfo('--- START LOG DATA SEND TO VNPAY ---');
      Helper::createLogInfo($inputData);
      Helper::createLogInfo('--- END LOG DATA SEND TO VNPAY  ---');

      ksort($inputData);
      $query = '';
      $i = 0;
      $hashdata = '';
      foreach ($inputData as $key => $value) {
        if ($i == 1) {
          $hashdata .= '&' . urlencode($key) . '=' . urlencode($value);
        } else {
          $hashdata .= urlencode($key) . '=' . urlencode($value);
          $i = 1;
        }
        $query .= urlencode($key) . '=' . urlencode($value) . '&';
      }

      $vnp_Url = $vnp_Url . '?' . $query;
      if (isset($vnp_HashSecret)) {
        $vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);
        $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
        return [
          'payment_url' => $vnp_Url
        ];
      }
    }
    return [
      'payment_url' => ''
    ];
  }

  public function resultPayment(Request $request)
  {
    $vnp_HashSecret = Config::get('constants.vnpay_payment_hashsecret');
    $params = $request->all();
    $vnp_SecureHash = isset($params['vnp_SecureHash']) ? $params['vnp_SecureHash'] : '';
    $inputData = [];
    foreach ($params as $key => $value) {
      if (substr($key, 0, 4) == 'vnp_') {
        $inputData[$key] = $value;
      }
    }

    // LOG DATA RECEIVED FROM VNPAY
    Helper::createLogInfo('--- RETURN URL: START LOG DATA RECEIVED FROM VNPAY ---');
    Helper::createLogInfo($inputData);
    Helper::createLogInfo('--- RETURN URL: END LOG DATA RECEIVED FROM VNPAY  ---');

    unset($inputData['vnp_SecureHash']);
    ksort($inputData);
    $i = 0;
    $hashData = '';
    foreach ($inputData as $key => $value) {
      if ($i == 1) {
        $hashData = $hashData . '&' . urlencode($key) . '=' . urlencode($value);
      } else {
        $hashData = $hashData . urlencode($key) . '=' . urlencode($value);
        $i = 1;
      }
    }

    $secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);
    // CHECK SIGNATURE
    if ($secureHash === $vnp_SecureHash) {
      if ($params['vnp_ResponseCode'] === '00') {
        return [
          'result' => __('message.success_payment_transaction')
        ];
      }
    }
    throw new HttpException(__('message.failed_payment_transaction'));
  }

  public function responsePayment($request)
  {
    $vnp_HashSecret = Config::get('constants.vnpay_payment_hashsecret');
    $params = $request->all();
    $inputData = [];
    foreach ($params as $key => $value) {
      if (substr($key, 0, 4) == 'vnp_') {
        $inputData[$key] = $value;
      }
    }

    // LOG DATA RECEIVED FROM VNPAY
    Helper::createLogInfo('--- IPN URL: START LOG DATA RECEIVED FROM VNPAY ---');
    Helper::createLogInfo($inputData);
    Helper::createLogInfo('--- IPN URL: END LOG DATA RECEIVED FROM VNPAY  ---');

    $vnp_SecureHash = isset($params['vnp_SecureHash']) ? $params['vnp_SecureHash'] : '';
    unset($inputData['vnp_SecureHash']);
    ksort($inputData);
    $i = 0;
    $hashData = '';
    foreach ($inputData as $key => $value) {
      if ($i == 1) {
        $hashData = $hashData . '&' . urlencode($key) . '=' . urlencode($value);
      } else {
        $hashData = $hashData . urlencode($key) . '=' . urlencode($value);
        $i = 1;
      }
    }
    $secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);
    $vnp_Amount = $inputData['vnp_Amount'] / 100;
    $vnp_OrderCode = $inputData['vnp_TxnRef'];
    // CHECK SIGNATURE
    if ($secureHash == $vnp_SecureHash) {
      $order = Order::where('code', $vnp_OrderCode)->first();
      if ($order) {
        if ((int)$order->amount === (int)$vnp_Amount) {
          if ($order->status === Config::get('constants.order_status.processing')) {
            if ($inputData['vnp_ResponseCode'] == '00' || $inputData['vnp_TransactionStatus'] == '00') {
              $order->paymentTransaction()->update([
                'status' => Config::get('constants.payment_transaction_status.completed')
              ]);
              $order->update([
                'status' => Config::get('constants.order_status.completed'),
                'payment_time' => Carbon::now()
              ]);
            } else {
              $order->paymentTransaction()->update([
                'status' => Config::get('constants.payment_transaction_status.failed')
              ]);
              $order->update([
                'status' => Config::get('constants.order_status.cancelled')
              ]);
            }
            return [
              'RspCode' => '00',
              'Message' => 'Confirm Success'
            ];
          } else {
            return [
              'RspCode' => '02',
              'Message' => 'Order already confirmed'
            ];
          }
        } else {
          return [
            'RspCode' => '04',
            'Message' => 'Invalid amount'
          ];
        }
      } else {
        return [
          'RspCode' => '01',
          'Message' => 'Order not found'
        ];
      }
    } else {
      return [
        'RspCode' => '97',
        'Message' => 'Invalid signature'
      ];
    }
    return [
      'RspCode' => '99',
      'Message' => 'Unknow error'
    ];
  }
}
