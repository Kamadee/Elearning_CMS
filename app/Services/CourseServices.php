<?php

namespace App\Services;

use App\Models\Customer;
use App\Helpers\Helper;
use App\Models\Course;
use App\Models\CourseVideo;
use App\Models\CourseCategoryPivot;
use App\Models\CourseTag;
use App\Models\HotContent;
use App\Models\Order;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Yajra\Datatables\Datatables;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CourseServices
{
  public function formatCourseDatatables($data)
  {
    return Datatables::of($data)
      ->addIndexColumn()
      ->addColumn('courseStatus', function ($row) {
        return __('course.status_list')[$row->status];
      })
      ->addColumn('courseThumbnail', function ($row) {
        if (!empty($row->thumbnail)) {
          return '<img style="width: 120px" src="' . $row->thumbnail . '" />';
        }
        return '<img style="width: 120px" src="' . url('/images/default_image.png') . '" />';
      })
      ->addColumn('courseBanner', function ($row) {
        return '<img style="width: 120px" src="' . $row->banner . '" />';
      })
      ->addColumn('originalPrice', function ($row) {
        return Helper::convertMoney($row->original_price);
      })
      ->addColumn('saleOffPrice', function ($row) {
        return Helper::convertMoney($row->sale_off_price);
      })
      ->addColumn('courseCategory', function ($row) {
        if ($row->courseCategories) {
          $html = '';
          foreach ($row->courseCategories as $category) {
            $html .= '<span class="badge bg-success mr-1">' . $category->category_name . '</span>';
          }
          return $html;
        }
        return '';
      })
      ->addColumn('action', function ($row) {
        $action = '';
        if (Helper::checkPermission('course.edit')) {
          $action .= '<a href="/courses/detail/' . $row->id . '" class="edit btn btn-primary btn-sm mr-1">' . __('course.detail_course') . '</a>';
        }
        if (Helper::checkPermission('course.delete')) {
          $action .= '<button data-id="' . $row->id . '" data-name="' . $row->title . '" class="btn-delete-course btn btn-danger btn-sm">' . __('course.delete_course') . '</button>';
        }
        return $action;
      })
      ->rawColumns(['action', 'courseCategory', 'courseThumbnail', 'courseBanner'])
      ->make(true);
  }

  public function formatHotCourseDatatables($data)
  {
    return Datatables::of($data)
      ->addColumn('check', function ($row) {
        return '<input type="checkbox" class="form-checkbox-input">';
      })
      ->addIndexColumn()
      ->addColumn('courseStatus', function ($row) {
        return __('course.status_list')[$row->status];
      })
      ->addColumn('courseThumbnail', function ($row) {
        if (!empty($row->thumbnail)) {
          return '<img style="width: 120px" src="' . $row->thumbnail . '" />';
        }
        return '<img style="width: 120px" src="' . url('/images/default_image.png') . '" />';
      })
      ->addColumn('courseBanner', function ($row) {
        return '<img style="width: 120px" src="' . $row->banner . '" />';
      })
      ->addColumn('originalPrice', function ($row) {
        return Helper::convertMoney($row->original_price);
      })
      ->addColumn('saleOffPrice', function ($row) {
        return Helper::convertMoney($row->sale_off_price);
      })
      ->addColumn('courseCategory', function ($row) {
        if ($row->courseCategories) {
          $html = '';
          foreach ($row->courseCategories as $category) {
            $html .= '<span class="badge bg-success mr-1">' . $category->category_name . '</span>';
          }
          return $html;
        }
        return '';
      })
      ->addColumn('action', function ($row) {
        $action = '';
        if (Helper::checkPermission('course.edit')) {
          $action .= '<a href="/courses/detail/' . $row->id . '" class="edit btn btn-primary btn-sm mr-1">' . __('course.detail_course') . '</a>';
        }
        if (Helper::checkPermission('course.delete')) {
          $action .= '<button data-id="' . $row->id . '" data-name="' . $row->title . '" class="btn-delete-course btn btn-danger btn-sm">' . __('course.delete_course') . '</button>';
        }
        return $action;
      })
      ->rawColumns(['check', 'action', 'courseCategory', 'courseThumbnail', 'courseBanner'])
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

  public function createHotCourseList($courseList)
  {
    DB::beginTransaction();
    try {
      $hotContentData = [];
      foreach ($courseList as $course) {
        $course = Course::find($course['id']);
        if ($course) {
          $courseId = $course['id'];
          $hotContentData[] = [
            'content_type' => 'course',
            'content_id' => $courseId,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
          ];
        }
      }
      if (!empty($hotContentData)) {
        HotContent::insert($hotContentData);
      }
      DB::commit();
      $courseList = HotContent::where('content_type', 'course')->with('course')->get();
      return [
        'status' => true,
        'courseList' => $courseList,
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

  public function processCreateCourse($formData)
  {
    try {
      $imageThumbnailUrl = [];
      if (isset($formData['input-pd']) && count($formData['input-pd']) > 0) {
        foreach ($formData['input-pd'] as $thumbnail) {
          $imageThumbnailUrl[] = $this->processSaveFileToStorage($thumbnail);
        }
      }
      $imageBannerUrl = [];
      if (isset($formData['input-banner-pd']) && count($formData['input-banner-pd']) > 0) {
        foreach ($formData['input-banner-pd'] as $banner) {
          $imageBannerUrl[] = $this->processSaveFileToStorage($banner);
        }
      }
      $courseData = [
        'title' => $formData['title'],
        'description' => $formData['description'],
        'thumbnail' => isset($imageThumbnailUrl[0]) ? $imageThumbnailUrl[0] : null,
        'banner' => isset($imageBannerUrl[0]) ? $imageBannerUrl[0] : null,
        'author' => $formData['author'],
        'authorDescription' => $formData['authorDescription'],
        'course_duration' => $formData['courseDuration'],
        'content' => $formData['content'],
        'status' => $formData['status'],
        'original_price' => $formData['originalPrice'] ? Helper::convertMoneyToNumber($formData['originalPrice']) : 0,
        'sale_off_price' => $formData['saleOffPrice'] ? Helper::convertMoneyToNumber($formData['saleOffPrice']) : 0,
        'created_at' => Carbon::now(),
        'updated_at' => Carbon::now()
      ];
      // dd($postData);
      DB::beginTransaction();
      $courseId = Course::insertGetId($courseData);
      if (isset($formData['courseCategories'])) {
        $dataCategories = [];
        foreach ($formData['courseCategories'] as $category) {
          $dataCategories[] = [
            'course_id' => $courseId,
            'post_category_id' => $category
          ];
          // dd($dataCategories);
          CourseCategoryPivot::insert($dataCategories);
        }
      }

      if (isset($formData['courseTags'])) {
        $dataTags = [];
        foreach ($formData['courseTags'] as $tag) {
          $dataTags[] = [
            'course_id' => $courseId,
            'tag_id' => $tag
          ];
          CourseTag::insert($dataTags);
        }
      }

      //   if (!empty($formData['video-list'])) {
      //     $videoDataToSave = [];
      //     $videoList = json_decode($formData['video-list']);
      //     if (!empty($videoList)) {
      //         foreach ($videoList as $video) {
      //             $videoDataToSave[] = [
      //                 'course_id' => $courseId,
      //                 'video_title' => $video->epTitle,
      //                 'video_description' => $video->epDescription,
      //                 'vimeo_id' => $video->vimeoId,
      //                 'video_thumbnail' => $video->epThumbnail,
      //                 'created_at' => Carbon::now()->toDateTimeString(),
      //                 'created_at' => Carbon::now()->toDateTimeString(),
      //             ];
      //         }
      //     }
      //     CourseVideo::insert($videoDataToSave);
      // }

      DB::commit();
      return [
        'status' => true,
        'message' => 'success',
        'id' => $courseId
      ];
    } catch (\Exception $e) {
      DB::rollback();
      return [
        'status' => false,
        'message' => $e->getMessage()
      ];
    }
  }

  public function processUpdateCourse($id, $formData)
  {
    try {
      $thumbnailUrls = [];
      if (isset($formData['input-pd']) && count($formData['input-pd']) > 0) {
        foreach ($formData['input-pd'] as $thumbnail) {
          $thumbnailUrls[] = $this->processSaveFileToStorage($thumbnail);
        }
      }
      $bannerUrls = [];
      if (isset($formData['input-banner-pd']) && count($formData['input-banner-pd']) > 0) {
        foreach ($formData['input-banner-pd'] as $banner) {
          $bannerUrls[] = $this->processSaveFileToStorage($banner);
        }
      }
      $currentData = Course::find($id);
      $courseData = [
        'title' => isset($formData['title']) ? $formData['title'] : $currentData->title,
        'description' => isset($formData['description']) ? $formData['description'] : $currentData->description,
        'author' => isset($formData['author']) ? $formData['author'] : $currentData->author,
        'authorDescription' => isset($formData['authorDescription']) ? $formData['authorDescription'] : $currentData->authorDescription,
        'course_duration' => isset($formData['courseDuration']) ? $formData['courseDuration'] : $currentData->course_duration,
        'content' => isset($formData['content']) ? $formData['content'] : $currentData->content,
        'status' => isset($formData['status']) ? $formData['status'] : $currentData->status,
        'original_price' => isset($formData['originalPrice']) ? Helper::convertMoneyToNumber($formData['originalPrice']) : $currentData->original_price,
        'sale_off_price' => isset($formData['saleOffPrice']) ? Helper::convertMoneyToNumber($formData['saleOffPrice']) : $currentData->sale_off_price,
        'updated_at' => Carbon::now()
      ];

      if (count($thumbnailUrls) > 0) {
        $courseData['thumbnail'] = $thumbnailUrls[0];
      }

      if (count($bannerUrls) > 0) {
        $courseData['banner'] = $bannerUrls[0];
      }
      DB::beginTransaction();

      Course::where('id', $id)->update($courseData);

      if (isset($formData['courseCategories'])) {
        $dataCategories = [];
        foreach ($formData['courseCategories'] as $category) {
          $dataCategories[] = [
            'course_id' => $id,
            'post_category_id' => $category
          ];
          // dd($dataCategories);
          CourseCategoryPivot::where('course_id', $id)->delete();
          CourseCategoryPivot::insert($dataCategories);
        }
      }

      if (isset($formData['courseTags'])) {
        $dataTags = [];
        foreach ($formData['courseTags'] as $tag) {
          $dataTags[] = [
            'course_id' => $id,
            'tag_id' => $tag
          ];
          CourseTag::insert($dataTags);
        }
      }

      // CourseVideo::where('course_id', $id)->delete();
      //   if(!empty($formData['video-list'])) {
      //     $courseVideoData = [];
      //     $videoList = json_decode($formData['video-list']);
      //     if(!empty($videoList)) {
      //       foreach($videoList as $video) {
      //         $courseVideoData[] = [
      //           'course_id' => $id,
      //           'video_title' => $video->epTitle,
      //           'video_description' => $video->epDescription,
      //           'vimeo_id' => $video->vimeoId,
      //           'video_thumbnail' => $video->epThumbnail,
      //           'updated_at' => Carbon::now()->toDateTimeString(),
      //         ];
      //       }
      //       CourseVideo::insert($courseVideoData);
      //     }
      //   }
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

  public function deleteFileFromStorage($filePath)
  {
    $fullPath = public_path($filePath);

    if (file_exists($fullPath)) {
      unlink($fullPath);
    } else {
      Log::error("File not found: " . $fullPath);
    }
  }

  public function processDeleteImage($id)
  {
    DB::beginTransaction();
    try {
      $course = Course::find($id);
      if ($course && $course->thumbnail) {
        $oldThumbnail = $course->thumbnail;
        $course->thumbnail = null;
        $this->deleteFileFromStorage($oldThumbnail);
      }
      // if ($course && $course->banner) {
      //   $oldBanner = $course->banner;
      //   $course->banner = null;
      //   $this->deleteFileFromStorage($oldBanner);
      // }
      $course->save();
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
  public function processDeleteBannerImage() {}
  public function deleteLocalPublicFile() {}
  public function processDeleteCourse($courseId)
  {
    try {
      DB::beginTransaction();
      $course = Course::find($courseId);
      $thumbnail = $course->thumbnail;
      $banner = $course->banner;
      $course->courseTags()->detach();
      $course->courseCategories()->detach();
      $course->videos()->delete();
      $course->delete();
      DB::commit();
      if ($thumbnail) {
        $this->deleteFileFromStorage($thumbnail);
      }
      if ($banner) {
        $this->deleteFileFromStorage($banner);
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

  public function processDeleteCourseHot($id)
  {
    DB::beginTransaction();
    try {
      $course = HotContent::where('content_id', $id)->first();
      if (!$course) {
        return [
          'status' => false,
          'message' => __('course.message.course_not_found')
        ];
      }
      $course->delete();
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

  public function processUploadImageToS3() {}

  public function getCourses($filterData)
  {
    $queries = Course::with(['courseCategories', 'courseTags']);
    if (isset($filterData['courseCategories']) && count($filterData['courseCategories']) > 0) {
      $queries->whereHas('courseCategories', function ($q) use ($filterData) {
        return $q->whereIn('post_categories.id', $filterData['courseCategories']);
      });
    }

    if (isset($filterData['tags']) && count($filterData['tags']) > 0) {
      $queries->whereHas('courseTags', function ($q) use ($filterData) {
        return $q->whereIn('tags.id', $filterData['tags']);
      });
    }

    if (isset($filterData['statusList']) && count($filterData['statusList']) > 0) {
      $queries->whereIn('status', $filterData['statusList']);
    }

    if (isset($filterData['keyword'])) {
      $queries->where(function ($q) use ($filterData) {
        $likeStr = '%' . Helper::escapeLike($filterData['keyword']) . '%';
        $q->where('courses.title', 'like', $likeStr)
          ->orWhere('courses.description', 'like', $likeStr)
          ->orWhere('courses.content', 'like', $likeStr);
      });
    }
    return $queries;
  }

  public function getCoursesList($filterData)
  {
    try {
      $results = Course::select(
        "id",
        "title",
        "description",
        "thumbnail",
        "banner",
        "author",
        "authorDescription",
        "course_duration",
        "content",
        "content",
        "original_price",
        "sale_off_price",
      )->with([
        'courseCategories:id, category_name',
        'courseTags: id, tag_name',
        'videos'
      ]);
      $perPage = !empty($filterData['per_page']) ? $filterData['per_page'] : config('constants.per_page');
      $page = !empty($filterData['page']) ? $filterData['page'] : config('constants.page');

      if (!empty($filterData['status'])) {
        return $results->whereIn('status', $filterData['status']);
      }
      if (!empty($filterData['category_name'])) {
        $results->whereHas('courseCategories', function ($q) use ($filterData) {
          $q->whereIn('category_name', $filterData['category_name']);
        });
      }
      if (!empty($filterData['tag_name'])) {
        $results->whereHas('courseTags', function ($q) use ($filterData) {
          $q->whereIn('tag_name', $filterData['tag_name']);
        });
      }
      if (isset($filterData['keyword'])) {
        $results->where(function ($q) use ($filterData) {
          $likeStr = '%' . Helper::escapeLike($filterData['keyword']) . '%';
          $q->where('courses.title', 'like', $likeStr)
            ->orWhere('courses.description', 'like', $likeStr)
            ->orWhere('courses.content', 'like', $likeStr);
        });
      }
      return $results->paginate($perPage, ['*'], '', $page);
    } catch (\Exception $e) {
      Log::error("Error in getCoursesList: " . $e->getMessage());
      return response()->json(['error' => $e->getMessage()], 500);
    }
  }

  // Hàm này giúp render ra all course, course nào đã dc mua sẽ đc format lại bằng is_bought = true
  // và ẩn ivdeo của course chưa đc mua
  public function formatCourseData($courseList)
  {
    $customerInfo = auth('customer')->user();
    if (empty($customerInfo)) {
      foreach ($courseList as &$course) {
        $course->is_bought = false;
        if ($course->original_price === $course->sale_off_price) {
          continue;
        }
        $course->videos = $this->__removeVideo($course->videos, true);
      }
      return $courseList;
    }

    $courseData = [];
    $orderByUserList = Order::where([
      ['customer_id', $customerInfo->id],
      ['status', config('constants.order_status.completed')],
    ])->with([
      'courses' => function ($q) {
        $q->where('status', config('constants.course_status_by_text.active'));
      }
    ])->get()->toArray();

    foreach ($orderByUserList as $orderByUser) {
      if (empty($orderByUser['course'])) {
        continue;
      }
      foreach ($orderByUser['course'] as $courseItem) {
        $courseData[] = $courseItem['id'];
      }
    }

    foreach ($courseList as &$course) {
      $course->is_bought = in_array($course->id, $courseData);
      if ($course->original_price === $course->sale_off_price) {
        continue;
      }

      if (!$course->is_bought) {
        $course->videos = $this->__removeVideo($course->videos, true);
      }
    }

    return $courseList;
  }

  private function __removeVideo($videoData, $hideVideo)
  {
    if (!empty($videoData) && $hideVideo) {
      foreach ($videoData as $video) {
        $video->vimeo_id = '';
      }
    }
    return $videoData;
  }

  public function getCourseById($id)
  {
    $courseDetail = Course::select([
      'id',
      'title',
      'description',
      'thumbnail',
      'banner',
      'author',
      'course_duration',
      'content',
      'status',
      'original_price',
      'sale_off_price'
    ])
      ->with([
        'courseCategories:id,category_name',
        'courseTags:id,tag_name',
        'videos'
      ])
      ->where('id', $id)
      ->first();
    if (!$courseDetail) {
      throw new NotFoundHttpException(__('message.not_found_course', [], 'Course not found'));
    }

    $customerInfo = auth('customer')->user();
    $courseDetail->is_bought = false;

    if ($customerInfo) {
      $isBought = Order::where([
        ['customer_id', $customerInfo->id],
        ['status', config('constants.order_status.completed')]
      ])->with(['courses'])
        ->whereHas('courses', function ($q) use ($id) {
          $q->where('courses.id', $id)
            ->where('status', config('constants.course_status_by_text.active'));
        })
        ->exists();
    }

    $courseDetail->is_bought = $isBought;

    if (!$isBought) {
      $courseDetail->videos = $this->__removeVideo($courseDetail->videos ?? [], true);
    }

    return $courseDetail;
  }

  public function getCourseTop()
  {
    try {
      $courseList = HotContent::where('content_type', 'course')->with('course')->get();
      return [
        'status' => true,
        'courseList' => $courseList,
        'message' => 'success'
      ];
    } catch (\Exception $e) {
      return [
        'status' => false,
        'message' => $e->getMessage()
      ];
    }
  }
}
