<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Services\CourseServices;
use Illuminate\Http\Request;
use App\Helpers\Helper;
use Illuminate\Support\Facades\Config;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CourseController extends Controller
{
  protected $courseServices;
  public function __construct(CourseServices $courseServices)
  {
    $this->courseServices = $courseServices;
  }
  public function getCourseList(Request $request)
  {
    try {
      $request->validate([
        "category_name" => ["nullable", 'array'],
        "tag_name" => ["nullable", 'array'],
        "status" => ["nullable", 'array'],
        "keyword" => ["nullable"],
        "page" => "nullable|numeric|min:1",
        "per_page" => "nullable|numeric|min:1",
      ]);
      $data = $this->courseServices->getCoursesList($request->all());
      return $this->successResponse([
        'data' => $this->courseServices->formatCourseData($data->items()),
        'total' => $data->total(),
        'count' => $data->count(),
        'per_page' => $data->perPage(),
        'current_page' => $data->currentPage(),
        'total_pages' => $data->lastPage()
      ]);
    } catch (ValidationException $e) {
      Helper::createLogError(__FILE__ . ':' .  __LINE__ . ' ' . $e);
      return $this->badRequestErrorResponse();
    } catch (\Exception $e) {
      Helper::createLogError(__FILE__ . ':' .  __LINE__ . ' ' . $e);
      return $this->internalServerErrorResponse();
    }
  }

  public function getCourseDetail($id)
  {
    try {
      $data = $this->courseServices->getCourseById($id);
      return $this->successResponse($data);
    } catch (\Exception $e) {
      Helper::createLogError(__FILE__ . ':' .  __LINE__ . ' ' . $e);
      return $this->internalServerErrorResponse();
    }
  }

  public function getCourseTop()
  {
    try {
      $data = $this->courseServices->getCourseTop();
      return $this->successResponse([
        'data' => $data['courseList']
      ]);
    } catch (\Exception $e) {
      Helper::createLogError(__FILE__ . ':' .  __LINE__ . ' ' . $e);
      return $this->internalServerErrorResponse();
    }
  }
}
