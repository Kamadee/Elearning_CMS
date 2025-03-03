<?php

namespace App\Http\Controllers;

use App\Services\VideoServices;
use App\Models\PostCategory;
use App\Models\VideoUploading;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\Datatables\Datatables;
use Vimeo\Laravel\Facades\Vimeo;
use Illuminate\Support\Facades\DB;

class VideoController extends Controller
{
  private $videoServices;

  public function __construct(VideoServices $videoServices)
  {
    $this->videoServices = $videoServices;
  }

  public function list()
  {
    return view('video.index');
  }
  public function anyData()
  {
    $filterData = [];
    $data = $this->videoServices->getVideos($filterData);
    $dataVideoTables = $this->videoServices->formatVideoDatatables($data);
    return $dataVideoTables;
  }
  public function create()
  {
    $currentTime = Carbon::now();
    $videoCategoryList = PostCategory::getPostCategoryList();
    return view('video.upload', ['currentTime' => $currentTime, 'videoCategoryList' => $videoCategoryList]);
  }

  public function uploadVideo(Request $request)
  {
    $result = $this->videoServices->processUploadChunkVideo($request);
    // dd($result);
    return $result;
  }

  public function processData(Request $request)
  {
    $videoList = $this->videoServices->getProcessUploadVideoList();
    $formatVideoData = $this->videoServices->formatVideoData($videoList);
    return $formatVideoData;
  }

  public function saveVideoId(Request $request)
  {
    $videoId = $request->fileId;
    $videoPath = $request->filePath;
    $result = $this->videoServices->saveVideoId($videoId, $videoPath);
    return $result;
  }

  public function vimeoDetail($id)
  {
    try {
      $videoRequestUrl = '/videos/' . $id;
      $video = Vimeo::request($videoRequestUrl, [], 'GET');

      if (empty($video['body']['error'])) {
        return ([
          'status' => true,
          'data' => $video['body']['embed']['html'],
          'message' => 'success'
        ]);
      }
      return ([
        'status' => false,
        'message' => 'Not Found'
      ]);
    } catch (\Exception $e) {
      return [
        'status' => false,
        'message' => $e->getMessage()
      ];
    }
  }

  public function processUpload()
  {
    return view('video.process');
  }

  public function fetchVimeoThumbnail()
  {
    DB::beginTransaction();
    try {
      $this->videoServices->updateThumbnail();
      DB::commit();
      return ([
        'status' => true,
        'data' => [],
        'message' => 'success'
      ]);
    } catch (\Exception $e) {
      DB::rollBack();
      return [
        'status' => false,
        'message' => $e->getMessage()
      ];
    }
  }

  // public function deleteVideo(Request $request)
  // {
  //   $validator = VideoUploading::where('id', $request->id);
  //   if (!$validator) {
  //     return [
  //       'status' => false,
  //       'message' => __('video.message.video_not_found')
  //     ];
  //   }
  //   return $this->videoServices->processDeleteVideo($request->id);
  // }
}
