<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Services\CourseServices;
use App\Models\Course;
use App\Models\Tag;
use App\Models\PostCategory;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Tymon\JWTAuth\Claims\Custom;

class CourseController extends Controller
{
  private $courseServices;

  public function __construct(CourseServices $courseServices)
  {
    $this->courseServices = $courseServices;
  }

  public function list()
  {
    $categoryList = PostCategory::select("id", "category_name")->get();
    $statusList = __('post.status_list');
    return view('course.index', [
      'categoryList' => $categoryList,
      'statusList' => $statusList
    ]);
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
      'title' => 'required|max:255',
      'description' => 'max:1000',
      'originalPrice' => 'required|numeric',
      'courseDuration' => 'required|numeric',
      'video-list' => 'required',
      'input-pd' => 'required',
      'input-banner-pd' => 'required',
      'author' => 'required',
      'status' => 'required'
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
}
