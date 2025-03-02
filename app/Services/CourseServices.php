<?php

namespace App\Services;

use App\Models\Customer;
use App\Helpers\Helper;
use App\Models\Course;
use App\Models\HotContent;
use App\Models\Order;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Yajra\Datatables\Datatables;

class CourseServices
{
  public function formatCourseDatatables($filterData)
  {
    return Datatables::of($filterData)
      ->addIndexColumn()
      ->addColumn('courseThumbnail', function ($row) {
        return $row->thumbnail ? '<img class="row-img" src="' . $row->thumbnail . '" alt="">' : '';
      })
      ->addColumn('courseBanner', function ($row) {
        return $row->banner ? '<img class="row-img" src="' . $row->banner . '" alt="">' : '';
      })
      ->addColumn('courseCategory', function ($row) {
        if ($row->courseCategory) {
          $html = '';
          foreach ($row->courseCategory as $category) {
            $html .= '<span class="badge bg-success mr-1">' . $category->category_name . '</span>';
          }
          return $html;
        }
        return '';
      })
      ->addColumn('courseStatus', function ($row) {
        $statusList = __('course.status');
        return isset($statusList[$row->status]) ? $statusList[$row->status] : 'Unknown';
        // return __('customer.status_list')[$row->status];
      })
      ->addColumn('action', function ($row) {
        $action = '';
        if (Helper::checkPermission('course.edit')) {
          $action .= '<a href="/courses/detail/' . $row->id . '" class="edit btn btn-primary btn-sm">' . __('course.update_course') . '</a>';
        }
        $action .= '<button data-id="' . $row->id . '" data-name="' . $row->title . '" class="btn-delete-course btn btn-danger btn-sm">' . __('course.delete_course') . '</button>';
        return $action;
      })
      ->rawColumns(['action', 'courseThumbnail', 'courseBanner', 'courseCategory', 'courseStatus'])
      ->make(true);
  }
  public function processUploadImage() {}
  public function processSaveFileToStorage() {}
  public function createHotCourseList() {}

  public function processCreateCourse() {}

  public function processUpdateCourse() {}

  public function processDeleteThumbnailImage() {}
  public function processDeleteBannerImage() {}
  public function deleteLocalPublicFile() {}
  public function processDeleteCourse() {}
  public function processUploadImageToS3() {}

  public function getCourses($filterData)
  {
    $queries = Course::with(['courseCategories']);
    if (isset($filterData['courseCategories']) && count($filterData['courseCategories']) > 0) {
      $queries->whereHas('courseCategories', function ($q) use ($filterData) {
        return $q->where('category_name', $filterData['courseCategories']);
      });
    }
    if (isset($filterData['statusList']) && count($filterData['statusList']) > 0) {
      return $queries->whereIn('status', $filterData['statusList']);
    }
    if (isset($filterData['keyword'])) {
      $queries->where(function ($q) use ($filterData) {
        $likeStr = '%' . Helper::escapeLike($filterData['keyword']) . '%';
        $q->where('courses.title', 'like', $likeStr)
          ->orWhere('courses.description', 'like', $likeStr)
          ->orWhere('courses.author', 'like', $likeStr);
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
