<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Services\CustomerServices;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Tymon\JWTAuth\Claims\Custom;

class CustomerController extends Controller
{
  private $customerServices;

  public function __construct(CustomerServices $customerServices)
  {
    $this->customerServices = $customerServices;
  }

  public function list(Request $request)
  {
    return view('customer.index');
  }

  public function anyData(Request $request)
  {
    $data = $this->customerServices->getCustomers();
    $datatableFormat = $this->customerServices->formatCustomerDatatables($data);
    return $datatableFormat;
  }

  public function detail(Request $request)
  {
    $customer = Customer::find($request->id);
    if ($customer) {
      $customerStatus = Config::get('constants.customer_status');
      return view('customer.detail', [
        'customer' => $customer,
        'customerStatus' => $customerStatus
      ]);
    }
  }

  public function update(Request $request)
  {
    $validator = Validator::make($request->all(), [
      "rank" => 'required|numeric',
    ]);
    if ($validator->fails()) {
      return redirect()->route('customer.detail', ['id' => $request->id])
        ->withErrors($validator)
        ->withInput();;
    }
    $result = $this->customerServices->processUpdateCustomer($request->id, $request->all());
    if ($result['status']) {
      return redirect()->route('customer.detail', ['id' => $request->id])
        ->withSuccess(__('customer.message.update_customer_success'));
    }
    return redirect()->route('customer.detail', ['id' => $request->id])
      ->withErrors($result['message'])
      ->withInput();
  }
}
