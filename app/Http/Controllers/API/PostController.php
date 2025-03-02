<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Services\PostServices;
use Illuminate\Http\Request;
use App\Helpers\Helper;
use Illuminate\Support\Facades\Config;

class PostController extends Controller
{
  private $postServices;
  public function __construct(PostServices $postServices)
  {
    $this->postServices = $postServices;
  }
  public function getPostList(Request $request)
  {

    try {
      $postList = $this->postServices->getPostPagination($request);
      if ($postList) {
        return response()->json([
          'status' => 200,
          'data' => $postList->items(),
          'total' => $postList->total(),
          'perPage' => $request->perPage ? (int)$request->perPage : Config::get('constants.per_page'),
          'page' => $request->page ? (int)$request->page : 1,
        ], 200);
      }
    } catch (\Exception $e) {
      dd($e);
      Helper::createLogError(__FILE__ . ':' .  __LINE__ . ' ' . $e);
      return $this->internalServerErrorResponse();
    }
  }

  public function getPostDetail(Request $request)
  {
    try {
      $post = Post::getPostRelationShipId($request->id);
      if ($post) {
        return response()->json([
          'status' => 200,
          'data'   => $post,
        ]);
      }
      return $this->notFoundErrorResponse();
    } catch (\Exception $e) {
      Helper::createLogError(__FILE__ . ':' .  __LINE__ . ' ' . $e);
      return $this->internalServerErrorResponse();
    }
  }
}
