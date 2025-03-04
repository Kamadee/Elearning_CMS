<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Services\CourseServices;
use App\Services\VideoServices;
use App\Models\Course;
use App\Models\HotContent;
use App\Models\Tag;
use App\Models\PostCategory;
use GuzzleHttp\Psr7\Request as Psr7Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Tymon\JWTAuth\Claims\Custom;

class CourseController extends Controller
{
  private $courseServices;
  protected $videoServices;

  public function __construct(CourseServices $courseServices, VideoServices $videoServices)
  {
    $this->courseServices = $courseServices;
    $this->videoServices = $videoServices;
  }

  public function hotCourse(Request $request) {
    $hotCourses = HotContent::where('content_type', 'course')->get();
    return view('course.hotCourse.blade', ['hotCourses' => $hotCourses]);
  }

  public function postHotCourse(Request $request) {
    $hotCourses = HotContent::where('content_type', 'course')->get();
    return view('course.hotCourse.blade', ['hotCourses' => $hotCourses]);
  }

  public function list(Request $request)
  {
      $validator = Validator::make($request->all(), [
      'id' => 'exists:courses,id',
      'title' => 'required|max:255',
      'author' => 'max:255',
      'description' => 'max:1000',
      'status' => 'required',
      'originalPrice' => 'max: 255',
      'saleOffPrice' => 'max: 255',
      'courseDuration' => 'max: 255'
    ]);
    if ($validator->fails()) {
      return redirect()->route('courses.create')
        ->withErrors($validator)
        ->withInput();;
    }
    $result = $this->courseServices->processCreateCourse($request->all());
    if ($result['status']) {
      $courseId = $result['id'];
      return redirect()->route('courses.detail', ['id' => $courseId])
        ->withSuccess(__('course.message.create_course_success'));
    }
    return redirect()->route('courses.create')
      ->withErrors($result['message'])
      ->withInput();
  }

  public function anyData(Request $request)
  {
    $filterData = [];
    if (isset($request->courseCategories) || isset($request->statusList)) {
      if ($request->courseCategories) {
        $filterData['courseCategories'] = $request->courseCategories;
      }
      if ($request->statusList) {
        $filterData['statusList'] = $request->statusList;
      }
    }
    $data = $this->courseServices->getCourses($filterData);
    $dataCourseTables = $this->courseServices->formatCourseDatatables($data);
    return $dataCourseTables;
  }

  public function create()
  {
    $tagList = Tag::all();
    $categoryList = PostCategory::select("id", "category_name")->get();
    $courseStatus = __('course.status_list');
    // dd($courseStatus);
    return view(
      'course.create',
      [
        'tagList' => $tagList,
        'categoryList' => $categoryList,
        'courseStatus' => $courseStatus
      ]
    );
  }

  public function createCourse(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'id' => 'exists:courses,id',
      'title' => 'required|max:255',
      'author' => 'max:255',
      'description' => 'max:1000',
      'status' => 'required',
      'originalPrice' => 'max: 255',
      'saleOffPrice' => 'max: 255',
      'courseDuration' => 'max: 255'
    ]);
    if ($validator->fails()) {
      return redirect()->route('courses.create')
        ->withErrors($validator)
        ->withInput();;
    }
    $result = $this->courseServices->processCreateCourse($request->all());
    if ($result['status']) {
      $courseId = $result['id'];
      return redirect()->route('courses.detail', ['id' => $courseId])
        ->withSuccess(__('course.message.create_course_success'));
    }
    return redirect()->route('courses.create')
      ->withErrors($result['message'])
      ->withInput();
  }

  public function detail(Request $request)
  {
    $course = Course::getCourseRelationShipById($request->id);
    $courseStatus = Config::get('constants.course_status');
    $categoryList = PostCategory::getPostCategoryList();
    $tagList = Tag::getTagList();
    return view('course/detail', [
      'course' => $course,
      'courseStatus' => $courseStatus,
      'categoryList' => $categoryList,
      'tagList' => $tagList
    ]);
  }

  public function uploadImage(Request $request) {
    $file = $request->upload;
    $url = $this->courseServices->processUploadImage($file);
    return response()->json(['fileName' => $url, 'uploaded' => 1, 'url' => $url,]);
  }

  public function updateCourse(Request $request) {
    $validator = Validator::make($request->all(), [
      'id' => 'exists:courses,id',
      'title' => 'required|max:255',
      'author' => 'max:255',
      'description' => 'max:1000',
      'status' => 'required',
      'originalPrice' => 'max: 255',
      'saleOffPrice' => 'max: 255',
      'courseDuration' => 'max: 255'
    ]);
    if ($validator->fails()) {
      return redirect()->route('cour.detail', ['id' => $request->id])
        ->withErrors($validator)
        ->withInput();;
    }
    $result = $this->courseServices->processUpdateCourse($request->id, $request->all());
    // dd($result);
    if ($result['status']) {
      return redirect()->route('courses.detail', ['id' => $request->id])
        ->withSuccess(__('course.message.update_course_success', ['id' => $request->id]));
    }
    return redirect()->route('courses.detail', ['id' => $request->id])
      ->withErrors($result['message'])
      ->withInput();
  }

  public function deleteThumbnai(Request $request) {
    $result = $this->courseServices->processDeleteImage($request->id);
    return $result;
  }

  public function deleteBanner(Request $request) {
    $result = $this->courseServices->processDeleteImage($request->id);
    return $result;
  }

  public function deleteCourse(Request $request) {
    $validator = Validator::make($request->all(), [
      'id' => 'exists:courses,id',
    ]);
    if ($validator->fails()) {
      return [
        'status' => false,
        'message' => __('course.message.course_not_found')
      ];
    }
    $courseId = $request->id;
    return $this->courseServices->processDeleteCourse($courseId);
  }
}
