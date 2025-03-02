<?php

namespace App\Services;

use App\Models\Tag;
use App\Models\Post;
use App\Models\PostCategory;
use Yajra\Datatables\Datatables;
use Carbon\Carbon;
use App\Helpers\Helper;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class CategoryServices
{
  // private $customerServices;

  // public function __construct(CustomerServices $customerServices)
  // {
  //   $this->customerServices = $customerServices;
  // }

  public function getCategory()
  {
    $queries = PostCategory::withCount('posts')->get();
    return $queries;
  }

  public function formatCategoryDatatables($data)
  {
    return Datatables::of($data)
      ->addIndexColumn()
      ->addColumn('action', function ($row) {
        $action = '';
        if (Helper::checkPermission('category.edit')) {
          $action .= '<a href="/category/detail/' . $row->id . '" class="edit btn btn-primary btn-sm">' . __('category.update_category') . '</a>';
        }
        if (Helper::checkPermission('category.delete')) {
          $action .= '<button data-id="' . $row->id . '" data-name="' . $row->category_name . '" class="btn-delete-category btn btn-danger btn-sm">' . __('category.delete_category') . '</button>';
        }
        return $action;
      })
      ->rawColumns(['action'])
      ->make(true);
  }

  public function processCreateCategory($formData)
  {
    try {
      $categoryData = [
        'category_name' => $formData['category_name'],
        'created_at' => Carbon::now(),
        'updated_at' => Carbon::now()
      ];
      // dd($postData);
      DB::beginTransaction();
      $categoryId = PostCategory::insertGetId($categoryData);

      DB::commit();
      return [
        'status' => true,
        'message' => 'success',
        'id' => $categoryId
      ];
    } catch (\Exception $e) {
      DB::rollback();
      return [
        'status' => false,
        'message' => $e->getMessage()
      ];
    }
  }

  public function processUpdateCategory($id, $formData)
  {
    try {
      $categoryData = [
        'category_name' =>  $formData['category_name'],
      ];
      DB::beginTransaction();
      PostCategory::where('id', $id)->update($categoryData);
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
  }

  public function processDeleteCategory($categoryId)
  {
    try {
      DB::beginTransaction();
      $category = PostCategory::find($categoryId);
      $category->posts()->detach();
      $category->delete();
      DB::commit();
      return [
        'status' => true,
        'message' => 'success'
      ];
    } catch (\Exception $e) {
      DB::rollback();
      return [
        'status' => false,
        'message' => $e->getMessage()
      ];
    }
  }
  // public function getCategoryPagination(Request $request)
  // {
  //   $conditionData = $this->getCategory($request->all());
  //   $sortColumn = [
  //     'category_name'
  //   ];
  //   $categories = Helper::renderSortData(
  //     $request,
  //     $sortData,
  //     $sortColumn
  //   );
  //   $perPage = $request->perPage ? $request->perPage : Config::getConstant('constants.per_page');
  //   $page = $request->page ? $request->page : Config::getConstant('constants.page');
  //   return $conditionData->paginate($perPage, ['*'], '', $page);
  // }


}
