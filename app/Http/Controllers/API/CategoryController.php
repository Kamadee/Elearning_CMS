<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Services\CategoryServices;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Helpers\Helper;
use Illuminate\Support\Facades\Config;

class CategoryController extends Controller
{
  private $categoryServices;
  public function __construct(CategoryServices $categoryServices)
  {
    $this->categoryServices = $categoryServices;
  }

  public function getCategoryPagination(Request $request)
  {
    // try {
    //   $data = $this->categoryServices->getCategoryList($request);
    //   return $this->successResponse($data);
    // } catch (\Exception $e) {
    //   Helper::createLogError(__FILE__ . ':' .  __LINE__ . ' ' . $e);
    //   return $this->internalServerErrorResponse();
    // }
  }

  public function getPostDetail(Request $request)
  {
    // try {
    //   $post = Post::getPostRelationShipId($request->id);
    // } catch (\Exception $e) {
    //   Helper::createLogError(__FILE__ . ':' .  __LINE__ . ' ' . $e);
    //   return $this->internalServerErrorResponse();
    // }
  }
}
