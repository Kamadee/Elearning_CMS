<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Order;
use Illuminate\Http\Request;
use App\Helpers\Helper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Yajra\Datatables\Datatables;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CustomerServices
{
  public function formatCustomerDatatables($data)
  {
    return Datatables::of($data)
      ->addIndexColumn()
      ->addColumn('name', function ($row) {
        return $row->first_name ? $row->first_name . ' ' . $row->last_name : '';
      })
      ->addColumn('image2D', function ($row) {
        return $row->avatar_2d ? '<img class="row-img" src="' . $row->avatar_2d . '" alt="">' : '';
      })
      ->addColumn('customerStatus', function ($row) {
        $statusList = __('customer.status_list');
        return isset($statusList[$row->status]) ? $statusList[$row->status] : 'Unknown';
        // return __('customer.status_list')[$row->status];
      })
      ->addColumn('action', function ($row) {
        return '<a href="/customer/detail/' . $row->id . '" class="edit btn btn-primary btn-sm">' . __('customer.edit_customer') . '</a>';
      })
      ->rawColumns(['action', 'image2D'])
      ->make(true);
  }

  public function processUpdateCustomer($id, $data)
  {
    try {
      $customerData = [
        'rank' => $data['rank'],
      ];
      DB::beginTransaction();
      if ($customerData) {
        Customer::where('id', $id)->update($customerData);
      }
      DB::commit();
      return [
        'status' => true,
        'message' => 'success',
      ];
    } catch (\Exception $e) {
      DB::rollback();
      return [
        'status' => false,
        'message' => $e->getMessage()
      ];
    }
  } // admin
  public function renderCustomerData() {} // admin
  public function updateCustomerData() {} // admin
  public function syncDataSSO() {}

  public function getCustomers()
  {
    $queries = Customer::all();
    return $queries;
  }

  public function verify($code)
  {
    $customer = Customer::where('confirmation_code', $code)->first();
    if ($customer) {
      if ($customer->status === Config::get('constants.customer_status_enable')) {
        throw new ConflictHttpException(__('message.customer_has_been_activated'));
      }
      // ACTIVE
      $customer->update([
        'status' => Config::get('constants.customer_status_enable'),
        'email_verified_at' => Carbon::now(),
        'confirmation_code' => null
      ]);
    } else {
      throw new NotFoundHttpException(__('message.not_exist_confirmation_code'));
    }
  }

  public function getOrders($request)
  {
    $customerId = auth('customer')->user()->id;
    $queries = Order::where('customer_id', $customerId)->orderBy('updated_at', 'DESC');
    if (isset($request->code) && $request->code) {
      $queries->where(function ($q) use ($request) {
        $likeStr = '%' . Helper::escapeLike($request->code) . '%';
        return $q->whereIn('code', $likeStr);
      });
    }
    if (isset($request->statusList) && $request->statusList) {
      $queries->where('status', $request->statusList);
    }
    $perPage = $request->perPage ? $request->perPage : Config::get('constants.per_page');
    $page = $request->page ? $request->page : Config::get('constants.page');
    $order = $queries->paginate($perPage, ['*'], '', $page); // paginate, items, total đều là hàm có sẵn
    // khi làm vc với Eloquent Query Builder, thuộc về Illuminate\Pagination\LengthAwarePaginator.
    return [
      'orders' => $order->items(),
      'total' => $order->total()
    ];
  }

  public function getOrderById($id)
  {
    $order = Order::with('orderItems')->where('order_id', $id)->first();
    return $order;
  }
}
