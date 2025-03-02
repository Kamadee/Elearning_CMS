<?php

namespace App\Http\Controllers;

use App\Models\PostCategory;
use App\Services\CategoryServices;
use App\Models\Tag;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
  private $categoryServices;

  public function __construct(CategoryServices $categoryServices)
  {
    $this->categoryServices = $categoryServices;
  }

  public function list()
  {
    return view('category.index');
  }

  public function anyData(Request $request)
  {
    $data = $this->categoryServices->getCategory();
    $dataCategoryTables = $this->categoryServices->formatCategoryDatatables($data);
    return $dataCategoryTables;
  }

  public function detail(Request $request)
  {
    $category = PostCategory::find($request->id);
    return view('category.detail', [
      'category' => $category,
    ]);
  }

  public function create()
  {
    return view('category.create');
  }

  public function createCategory(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'category_name' => 'required|max:255',
    ]);
    if ($validator->fails()) {
      return redirect()->route('category.create')
        ->withErrors($validator)
        ->withInput();;
    }
    $result = $this->categoryServices->processCreateCategory($request->all());
    if ($result['status']) {
      $categoryId = $result['id'];
      return redirect()->route('category.detail', ['id' => $categoryId])
        ->withSuccess(__('category.message.create_category_success'));
    }
    return redirect()->route('category.create')
      ->withErrors($result['message'])
      ->withInput();
  }

  public function updateCategory(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'id' => 'exists:tags,id',
      'category_name' => 'required|max:255',
    ]);
    if ($validator->fails()) {
      return redirect()->route('category.detail', ['id' => $request->id])
        ->withErrors($validator)
        ->withInput();;
    }
    $result = $this->categoryServices->processUpdateCategory($request->id, $request->all());
    // dd($result);
    if ($result['status']) {
      return redirect()->route('category.detail', ['id' => $request->id])
        ->withSuccess(__('category.message.update_category_success', ['id' => $request->id]));
    }
    return redirect()->route('category.detail', ['id' => $request->id])
      ->withErrors($result['message'])
      ->withInput();
  }

  public function deleteCategory(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'id' => 'exists:post_categories,id',
    ]);
    if ($validator->fails()) {
      return [
        'status' => false,
        'message' => __('category.message.category_not_found')
      ];
    }
    $categoryId = $request->id;
    return $this->categoryServices->processDeleteCategory($categoryId);
  }
}
