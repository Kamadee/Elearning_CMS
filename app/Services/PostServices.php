<?php

namespace App\Services;

use App\Models\Post;
use App\Models\PostCategoryPivot;
use App\Helpers\Helper;
use App\Models\PostTag;
use Carbon\Carbon;
use App\Services\S3Services;
use Illuminate\Support\Facades\Config;
use Yajra\Datatables\Datatables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PostServices
{
  protected $S3Services;

  public function __construct(S3Services $S3Services)
  {
    $this->S3Services = $S3Services;
  }

  public function formatPostDatatables($filterData)
  {
    return Datatables::of($filterData)
      ->addIndexColumn()
      ->addColumn('name', function ($row) {
        return $row->first_name ? $row->first_name . ' ' . $row->last_name : '';
      })
      ->addColumn('postThumbnail', function ($row) {
        return $row->thumbnail ? '<img class="row-img" src="' . $row->thumbnail . '" alt="">' : '';
      })
      ->addColumn('postCategory', function ($row) {
        if ($row->postCategories) {
          $html = '';
          foreach ($row->postCategories as $category) {
            $html .= '<span class="badge bg-success mr-1">' . $category->category_name . '</span>';
          }
          return $html;
        }
        return '';
      })
      ->addColumn('postStatus', function ($row) {
        $statusList = __('post.status_list');
        return isset($statusList[$row->status]) ? $statusList[$row->status] : 'Unknown';
        // return __('customer.status_list')[$row->status];
      })
      ->addColumn('action', function ($row) {
        $action = '';
        if (Helper::checkPermission('post.edit')) {
          $action .= '<a href="/posts/detail/' . $row->id . '" class="edit btn btn-primary btn-sm">' . __('post.update_post') . '</a>';
        }
        if (Helper::checkPermission('post.delete')) {
          $action .= '<button data-id="' . $row->id . '" data-name="' . $row->title . '" class="btn-delete-post btn btn-danger btn-sm">' . __('post.delete_post') . '</button>';
        }
        return $action;
      })
      ->rawColumns(['action', 'postThumbnail', 'postCategory'])
      ->make(true);
  }

  public function processUploadImage($image)
  {
    return $this->processSaveFileToStorage($image);
  }

  public function processSaveFileToStorage($image)
  {
    $fileImage = time() . '_' . $image->getClientOriginalName();
    $image->move(public_path('uploads'), $fileImage);
    return '/uploads/' . $fileImage;
  }

  public function processCreatePost($formData)
  {
    try {
      $imageUrl = [];
      if (isset($formData['input-pd']) && count($formData['input-pd']) > 0) {
        foreach ($formData['input-pd'] as $image) {
          $imageUrl[] = $this->processSaveFileToStorage($image);
        }
        // dd($imageUrl);
      }
      $postData = [
        'title' => $formData['title'],
        'description' => $formData['description'],
        'thumbnail' => isset($imageUrl[0]) ? $imageUrl[0] : null,
        'content' => $formData['content'],
        'status' => $formData['status'],
        'created_at' => Carbon::now(),
        'updated_at' => Carbon::now()
      ];
      // dd($postData);
      DB::beginTransaction();
      $postId = Post::insertGetId($postData);
      if (isset($formData['postCategories'])) {
        $dataCategories = [];
        foreach ($formData['postCategories'] as $category) {
          $dataCategories[] = [
            'post_id' => $postId,
            'post_category_id' => $category
          ];
          // dd($dataCategories);
          PostCategoryPivot::insert($dataCategories);
        }
      }

      if (isset($formData['postTags'])) {
        $dataTags = [];
        foreach ($formData['postTags'] as $tag) {
          $dataTags[] = [
            'post_id' => $postId,
            'tag_id' => $tag
          ];
          PostTag::insert($dataTags);
        }
      }

      DB::commit();
      return [
        'status' => true,
        'message' => 'success',
        'id' => $postId
      ];
    } catch (\Exception $e) {
      DB::rollback();
      return [
        'status' => false,
        'message' => $e->getMessage()
      ];
    }
  }

  public function processUpdatePost($id, $formData)
  {
    try {
      $imageUrl = [];
      if (isset($formData['input-pd']) && count($formData['input-pd']) > 0) {
        foreach ($formData['input-pd'] as $image) {
          $imageUrl[] = $this->processSaveFileToStorage($image);
        }
        // dd($imageUrl);
      }
      $postData = [
        'title' =>  $formData['title'],
        'description' =>  $formData['description'],
        'content' =>  $formData['content'],
        'thumbnail' =>  isset($imageUrl[0]) ? $imageUrl[0] : null,
        'status' =>  $formData['status']
      ];
      DB::beginTransaction();

      Post::where('id', $id)->update($postData);

      if (isset($formData['postCategories'])) {
        $dataCategories = [];
        foreach ($formData['postCategories'] as $category) {
          $dataCategories[] = [
            'post_id' => $id,
            'post_category_id' => $category
          ];
          // dd($dataCategories);
          PostCategoryPivot::where('post_id', $id)->delete();
          PostCategoryPivot::insert($dataCategories);
        }
      }

      if (isset($formData['postTags'])) {
        $dataTags = [];
        foreach ($formData['postTags'] as $tag) {
          $dataTags[] = [
            'post_id' => $id,
            'tag_id' => $tag
          ];
          PostTag::where('post_id', $id)->delete();
          PostTag::insert($dataTags);
        }
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
  }

  public function processDeleteThumbnailImage($id)
  {
    try {
      $post = Post::find($id);
      if ($post && $post->thumbnail) {
        $oldThumbnail = $post->thumbnail;
        DB::beginTransaction();
        $post->thumbnail = null;
        $post->save();
        DB::commit();
        $this->deleteFileFromStorage($oldThumbnail);
      }
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

  public function deleteFileFromStorage($filePath)
  {
    $fullPath = public_path($filePath); // Tạo đường dẫn đầy đủ đến file

    if (file_exists($fullPath)) {
      unlink($fullPath); // Xóa file
    } else {
      // Log lỗi hoặc thông báo nếu file không tìm thấy
      Log::error("File not found: " . $fullPath);
    }
  }

  public function processDeletePost($postId)
  {
    try {
      DB::beginTransaction();
      $post = Post::find($postId);
      $thumbnail = $post->thumbnail;
      $post->postCategories()->detach();
      $post->postTags()->detach();
      $post->delete();
      DB::commit();
      if ($thumbnail) {
        $this->deleteFileFromStorage($thumbnail);
      }
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

  public function processUploadImageToS3() {}



  public function getPost($filterData)
  {
    $queries = Post::with(['postCategories', 'postTags']);
    if (isset($filterData['postCategories']) && is_array($filterData['postCategories'])) {
      $queries->whereHas('postCategories', function ($q) use ($filterData) {
        return $q->whereIn('post_categories.id', $filterData['postCategories']);
      });
    }

    if (isset($filterData['tags']) && count($filterData['tags']) > 0) {
      $queries->whereHas('postTags', function ($q) use ($filterData) {
        return $q->whereIn('tags.id', $filterData['tags']);
      });
    }

    if (isset($filterData['statusList']) && is_array($filterData['statusList'])) {
      $queries->where('status', $filterData['statusList']);
    }

    if (isset($filterData['keyword'])) {
      $queries->where(function ($q) use ($filterData) {
        $likeStr = '%' . Helper::escapeLike($filterData['keyword']) . '%';
        $q->where('posts.title', 'like', $likeStr)
          ->orWhere('posts.description', 'like', $likeStr)
          ->orWhere('posts.content', 'like', $likeStr);
      });
    }

    // if (!isset($filterData)) {
    //   $queries = Post::select(
    //     "id",
    //     "title",
    //     "description",
    //     "thumbnail",
    //     "category_name",
    //     "tag_name",
    //     "status",
    //     "created_at",
    //     "updated_at",
    //   )->with(['postCategories', 'postTags']);
    // }

    return $queries;
  }

  public function getPostPagination($request)
  {
    $conditionData = $this->getPost($request->all());
    $sortColumns = [
      'title',
      'description',
      'updated_at',
      'created_at',
    ];

    $posts = Helper::renderSortData(
      $request,
      $conditionData,
      $sortColumns
    );

    $perPage = $request->perPage ? $request->perPage : Config::get('constants.per_page');
    $page = $request->page ? $request->page : Config::get('constants.page');
    return $posts->paginate($perPage, ['*'], '', $page);
  }
}
