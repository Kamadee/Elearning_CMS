<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Services\PostServices;
use App\Models\PostCategory;
use App\Models\Tag;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class PostController extends Controller
{
  private $postServices;

  public function __construct(PostServices $postServices)
  {
    $this->postServices = $postServices;
  }

  public function list()
  {
    $categoryList = PostCategory::select("id", "category_name")->get();
    $statusList = __('post.status_list');
    return view('post.index', [
      'categoryList' => $categoryList,
      'statusList' => $statusList
    ]);
  }

  public function anyData(Request $request)
  {
    $filterData = [];
    if (isset($request->postCategories) || isset($request->statusList)) {
      if ($request->postCategories) {
        $filterData['postCategories'] = $request->postCategories;
      }
      if ($request->statusList) {
        $filterData['statusList'] = $request->statusList;
      }
    }
    $data = $this->postServices->getPost($filterData);
    $dataPostTables = $this->postServices->formatPostDatatables($data);
    // dd($dataPostTables);
    return $dataPostTables;
  }

  public function detail(Request $request)
  {
    $post = Post::getPostRelationShipById($request->id);
    $postStatus = Config::get('constants.post_status');
    $categoryList = PostCategory::getPostCategoryList();
    $tagList = Tag::getTagList();
    return view('post.detail', [
      'post' => $post,
      'postStatus' => $postStatus,
      'categoryList' => $categoryList,
      'tagList' => $tagList
    ]);
  }

  public function create()
  {
    $tagList = Tag::all();
    $categoryList = PostCategory::select("id", "category_name")->get();
    $postStatus = Config::get('constants.post_status');
    return view(
      'post.create',
      [
        'tagList' => $tagList,
        'categoryList' => $categoryList,
        'postStatus' => $postStatus
      ]
    );
  }

  public function createPost(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'title' => 'required|max:255',
      'description' => 'max:1000',
      'content' => 'required',
      'input-pd' => 'required',
      'status' => 'required'
    ]);
    if ($validator->fails()) {
      return redirect()->route('posts.create')
        ->withErrors($validator)
        ->withInput();;
    }
    $result = $this->postServices->processCreatePost($request->all());
    if ($result['status']) {
      $postId = $result['id'];
      return redirect()->route('posts.detail', ['id' => $postId])
        ->withSuccess(__('post.message.create_post_success'));
    }
    return redirect()->route('posts.create')
      ->withErrors($result['message'])
      ->withInput();
  }

  public function updatePost(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'id' => 'exists:posts,id',
      'title' => 'required|max:255',
      'description' => 'max:1000',
      'content' => 'required',
      'status' => 'required'
    ]);
    if ($validator->fails()) {
      return redirect()->route('posts.detail', ['id' => $request->id])
        ->withErrors($validator)
        ->withInput();;
    }
    $result = $this->postServices->processUpdatePost($request->id, $request->all());
    // dd($result);
    if ($result['status']) {
      return redirect()->route('posts.detail', ['id' => $request->id])
        ->withSuccess(__('post.message.update_post_success', ['id' => $request->id]));
    }
    return redirect()->route('posts.detail', ['id' => $request->id])
      ->withErrors($result['message'])
      ->withInput();
  }

  public function deletePost(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'id' => 'exists:posts,id',
    ]);
    if ($validator->fails()) {
      return [
        'status' => false,
        'message' => __('post.message.post_not_found')
      ];
    }
    $postId = $request->id;
    return $this->postServices->processDeletePost($postId);
  }

  public function uploadImage(Request $request)
  {
    $file = $request->upload;
    $url = $this->postServices->processUploadImage($file);
    return response()->json(['fileName' => $url, 'uploaded' => 1, 'url' => $url,]);
  }

  public function deleteThumbnai(Request $request)
  {
    $result = $this->postServices->processDeleteThumbnailImage($request->id);
    return $result;
  }
}
