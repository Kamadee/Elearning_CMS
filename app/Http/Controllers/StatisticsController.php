<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\OrderServices;
use App\Models\Customer;
use App\Models\Course;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PostCategory;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use App\Helpers\Helper;
use Barryvdh\DomPDF\Facade as PDF;

class StatisticsController extends Controller
{
  // public function statisticsPage(Request $request)
  // {
  //   $totalCustomer = Customer::where('status', Config::get('constants.customer_status_enable'))->count();
  //   $totalCourse = Course::where('status', Config::get('constants.course_status_by_text.active'))->count();
  //   $totalOrder = Order::where('status', Config::get('constants.order_status.completed'))->count();
  //   $revenue = Order::where('status', Config::get('constants.order_status.completed'))->sum('amount');
  //   // dd($totalCustomer, $totalCourse, $totalOrder, $revenue);
  //   $monthlyRevenue = array_fill(1, 12, 0);
  //   $revenueData = DB::table('orders')
  //     ->where('status', Config::get('constants.order_status.completed'))
  //     ->select(DB::raw('MONTH(created_at) as month'), DB::raw('SUM(amount) as total'))
  //     ->groupBy(DB::raw('MONTH(created_at)'))
  //     ->orderBy('month', 'asc')
  //     ->get();
  //   foreach ($revenueData as $data) {
  //     $monthlyRevenue[$data->month] = $data->total / 1000000;
  //   }
  //   $topPurchasedCourses = Course::withCount('items')->get();
  //   //////////////////////////
  //   $revenueCategory = OrderItem::select('categories.category_name', DB::raw('SUM(order_items.price) as total_revenue'))
  //     ->join('courses', 'order_items.course_id', '=', 'courses.id')
  //     ->join('course_category_pivot', 'courses.id', '=', 'course_category_pivot.course_id')
  //     ->join('post_categories as categories', 'course_category_pivot.post_category_id', '=', 'categories.id')
  //     ->groupBy('categories.category_name')
  //     ->get();

  //   $totalRevenue = $revenueCategory->sum('total_revenue');

  //   $revenueByCategory = [];
  //   foreach ($revenueCategory as $data) {
  //     $revenueByCategory[$data->category_name] = $data->total_revenue;
  //   }
  //   $categories = PostCategory::select('category_name')->get()->toArray();

  //   foreach ($categories as &$category) {
  //     $categoryName = $category['category_name'];
  //     $totalRevenueByCategory   = $revenueByCategory[$categoryName]  ?? 0;
  //     $category['total_revenue'] = $totalRevenueByCategory  / 1000000;
  //     $category['percentage'] = ($totalRevenueByCategory  / $totalRevenue) * 100;
  //   }
  //   // dd($categories);
  //   /////////////////////////
  //   $monthlyCustomer = array_fill(1, 12, 0);
  //   $customerData = DB::table('customers')
  //     ->where('status', Config::get('constants.customer_status_enable'))
  //     ->select(DB::raw('MONTH(created_at) as month'), DB::raw('COUNT(*) as total_customer'))
  //     ->groupBy(DB::raw('MONTH(created_at)'))
  //     ->orderBy('month', 'asc')
  //     ->get()->toArray();
  //   foreach ($customerData as $data) {
  //     $monthlyCustomer[$data->month] = $data->total_customer;
  //   }
  //   // dd($monthlyCustomer);
  //   return view('home', [
  //     'totalCustomer' => $totalCustomer,
  //     'totalCourse' => $totalCourse,
  //     'totalOrder' => $totalOrder,
  //     'revenue' => $revenue,
  //     'monthlyRevenue' => $monthlyRevenue,
  //     'topPurchasedCourses' => $topPurchasedCourses,
  //     'categories' => $categories,
  //     'monthlyCustomer' => $monthlyCustomer
  //   ]);
  // }

  public function getStatisticsData()
  {
    $totalCustomer = Customer::where('status', Config::get('constants.customer_status_enable'))->count();
    $totalCourse = Course::where('status', Config::get('constants.course_status_by_text.active'))->count();
    $totalOrder = Order::where('status', Config::get('constants.order_status.completed'))->count();
    $revenue = Order::where('status', Config::get('constants.order_status.completed'))->sum('amount');

    $monthlyRevenue = array_fill(1, 12, 0);
    $revenueData = DB::table('orders')
      ->where('status', Config::get('constants.order_status.completed'))
      ->select(DB::raw('MONTH(created_at) as month'), DB::raw('SUM(amount) as total'))
      ->groupBy(DB::raw('MONTH(created_at)'))
      ->orderBy('month', 'asc')
      ->get();
    foreach ($revenueData as $data) {
      $monthlyRevenue[$data->month] = $data->total / 1000000;
    }

    $topPurchasedCourses = $this->topPurchasedCourses();

    $revenueCategory = OrderItem::select('categories.category_name', DB::raw('SUM(order_items.price) as total_revenue'))
      ->join('courses', 'order_items.course_id', '=', 'courses.id')
      ->join('course_category_pivot', 'courses.id', '=', 'course_category_pivot.course_id')
      ->join('post_categories as categories', 'course_category_pivot.post_category_id', '=', 'categories.id')
      ->groupBy('categories.category_name')
      ->get();

    $totalRevenue = $revenueCategory->sum('total_revenue');
    $revenueByCategory = [];
    foreach ($revenueCategory as $data) {
      $revenueByCategory[$data->category_name] = $data->total_revenue;
    }
    $categories = PostCategory::select('category_name')->get()->toArray();
    foreach ($categories as &$category) {
      $categoryName = $category['category_name'];
      $totalRevenueByCategory = $revenueByCategory[$categoryName] ?? 0;
      $category['total_revenue'] = $totalRevenueByCategory / 1000000;
      if($totalRevenue != 0) {
        $category['percentage'] = ($totalRevenueByCategory / $totalRevenue) * 100;
      } else {
        $category['percentage'] = 0;
      }
    }

    $monthlyCustomer = array_fill(1, 12, 0);
    $customerData = DB::table('customers')
      ->where('status', Config::get('constants.customer_status_enable'))
      ->select(DB::raw('MONTH(created_at) as month'), DB::raw('COUNT(*) as total_customer'))
      ->groupBy(DB::raw('MONTH(created_at)'))
      ->orderBy('month', 'asc')
      ->get()->toArray();
    foreach ($customerData as $data) {
      $monthlyCustomer[$data->month] = $data->total_customer;
    }

    return [
      'totalCustomer' => $totalCustomer,
      'totalCourse' => $totalCourse,
      'totalOrder' => $totalOrder,
      'revenue' => $revenue,
      'monthlyRevenue' => $monthlyRevenue,
      'topPurchasedCourses' => $topPurchasedCourses,
      'categories' => $categories,
      'monthlyCustomer' => $monthlyCustomer
    ];
  }

  public function topPurchasedCourses()
  {
    return Course::query()
      ->select([
        'courses.id',
        'courses.thumbnail',
        'courses.title',
        'courses.author',
        'courses.original_price',
        'courses.sale_off_price',
        DB::raw('SUM(order_items.quantity) as items_count'),
      ])
      ->join('order_items', 'order_items.course_id', '=', 'courses.id')
      ->join('orders', 'orders.id', '=', 'order_items.order_id')
      ->where('orders.status', Config::get('constants.order_status.completed'))
      ->groupBy([
        'courses.id',
        'courses.thumbnail',
        'courses.title',
        'courses.author',
        'courses.original_price',
        'courses.sale_off_price',
      ])
      ->havingRaw('SUM(order_items.quantity) >= 1')
      ->orderByDesc('items_count')
      ->orderBy('courses.id')
      ->limit(10)
      ->get();
  }

  public function statisticsPage()
  {
    $data = $this->getStatisticsData();
    // dd($data);
    return view('home', ['data' => $data]);
  }

  public function exportPDF(Request $request)
  {
    $imagesData = json_decode($request->image);
    $imagesDataAfter = [];

    foreach ($imagesData as $image) {
      $image = str_replace('data:image/png;base64,', '', $image);
      $image = base64_decode($image);

      $fileName = 'chart_' . uniqid() . '.png';
      $imagePath = public_path('temp/' . $fileName);

      file_put_contents($imagePath, $image);

      $imageUrl = asset('temp/' . $fileName);
      $imagesDataAfter[] = $imageUrl;
    }
    $data = $this->getStatisticsData();
    $pdf = PDF::loadView('statistics.print', ['data' => $data, "imagesDataAfter" => $imagesDataAfter]);

    return $pdf->download('dashboard_statistics.pdf');
  }
}
