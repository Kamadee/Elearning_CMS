<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\VideoServices;
use Illuminate\Http\Request;
use App\Helpers\Helper;
use Illuminate\Support\Facades\DB;
use App\Helpers\ResponseCode;
use Illuminate\Http\Response;

class VideoController extends Controller
{
  private $videoServices;
  public function __construct(VideoServices $videoServices)
  {
    $this->videoServices = $videoServices;
  }

  public function getDetailVimeo(Request $request)
  {
    DB::beginTransaction();
    try {
      $data = $this->videoServices->getDetailVimeo($request);
      DB::commit();
      return $this->successResponse($data);
    } catch (\Exception $e) {
      Helper::createLogError(__FILE__ . ':' .  __LINE__ . ' ' . $e);
      DB::rollBack();
      return $this->internalServerErrorResponse();
    }
  }
}
