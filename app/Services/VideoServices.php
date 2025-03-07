<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Tag;
use App\Models\Post;
use App\Helpers\Helper;
use App\Services\S3Services;
use App\Models\VideoUploading;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use Yajra\Datatables\Datatables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Jobs\UploadToVimeo;
use Vimeo\Laravel\Facades\Vimeo;
use Illuminate\Http\Request;

class VideoServices
{
  protected $S3Services;
  public function __construct(S3Services $S3Services)
  {
    $this->S3Services = $S3Services;
  }
  public function getVideos()
  {
    $videos = VideoUploading::where('job_status', config('constants.job_status.success'))
      ->orderBy('job_id', 'desc')->get();
    return $videos;
  }

  public function formatVideoData($data)
  {
    return Datatables::of($data)
      ->addIndexColumn()
      ->addColumn('video_id', function ($row) {
        return $row->video_id;
      })
      ->addColumn('created_at', function ($row) {
        return $row->created_at;
      })
      ->addColumn('error_log', function ($row) {
        return $row->error_log;
      })
      ->addColumn('job_status', function ($row) {
        switch ($row->job_status) {
          case config('constants.job_status.inProgress'):
            return '<div class="badge bg-warning">Đang tải lên</div>';
          case config('constants.job_status.success'):
            return '<div class="badge bg-success">Thành công</div>';
          case config('constants.job_status.fail'):
            return '<div class="badge bg-danger">Thất bại</div>';
          default:
            return '';
        }
      })
      ->addColumn('action', function ($row) {
        if ($row->job_status === config('constants.job_status.fail')) {
          return '<button type="button" class="btn btn-block btn-warning">Cập nhật</button>';
        }
        return '';
      })
      ->rawColumns(['job_status', 'action'])
      ->make(true);
  }

  public function formatVideoDatatables($filterData)
  {
    return Datatables::of($filterData)
      ->addIndexColumn()
      ->addColumn('title', function ($row) {
        return $row->video_id;
      })
      ->addColumn('videoThumbnail', function ($row) {
        if (!empty($row->thumbnail_id)) {
          return '<img style="width: 120px;height: 120px;" src="' . $row->thumbnail_id . '" />';
        }
        return '<img style="width: 120px;height: 120px;" src="' . url('/images/default_image.jpg') . '" />';
      })
      ->addColumn('action', function ($row) {
        return '<button type="button" class="btn btn-block btn-info btn-info-video" style="width: 130px;" video-id="' . $row->vimeo_id . '">Xem video</button>';
      })
      ->rawColumns(['action', 'videoThumbnail'])
      ->make(true);
  }

  public function getProcessUploadVideoList()
  {
    $videoList = VideoUploading::all();
    return $videoList;
  }
  // tạo dữ liệu preview cho từng file được upload, giúp cho các chức năng khác trong ứng dụng dễ dàng sử dụng
  public function processUploadChunkVideo(Request $data)
  {
    $preview = $config = $errors = [];
    $targetDir = public_path('uploads');
    if (!file_exists($targetDir)) {
      @mkdir($targetDir);
    }

    $fileBlob = 'fileBlob';
    if (isset($_FILES[$fileBlob])) {
      $file = $_FILES[$fileBlob]['tmp_name'];
      $fileName = $_POST['fileName'];
      $fileSize = $_POST['fileSize'];
      $fileId = $_POST['fileId'];
      $index =  $_POST['chunkIndex'];
      $totalChunks = $_POST['chunkCount'];     // the total number of chunks for this file
      $targetFile = $targetDir . '/' . $fileName;  // your target file path
      if ($totalChunks > 1) {                  // create chunk files only if chunks are greater than 1
        $targetFile .= '_' . str_pad($index, 4, '0', STR_PAD_LEFT);
      }

      $uploadResult = move_uploaded_file($file, $targetFile);
      if ($uploadResult) {
        $chunks = glob("{$targetDir}/{$fileName}_*");
        // check uploaded chunks so far (do not combine files if only one chunk received)
        $allChunksUploaded = $totalChunks > 1 && count($chunks) == $totalChunks;
        if ($allChunksUploaded) {           // all chunks were uploaded
          $outFile = $targetDir . '/' . $fileName;
          // combines all file chunks to one file
          $this->combineChunks($chunks, $outFile);
        }
        // if you wish to generate a thumbnail image for the file
        // $targetUrl = getThumbnailUrl($path, $fileName);
        // separate link for the full blown image file
        $zoomUrl = '/uploads/' . $fileName;

        return [
          'chunkIndex' => $index,         // the chunk index processed
          'initialPreview' => '', // the thumbnail preview data (e.g. image)
          'initialPreviewConfig' => [
            [
              'type' => 'image',      // check previewTypes (set it to 'other' if you want no content preview)
              'caption' => $fileName, // caption
              'key' => $fileId,       // keys for deleting/reorganizing preview
              'fileId' => $fileId,    // file identifier
              'size' => $fileSize,    // file size
              'zoomData' => $zoomUrl, // separate larger zoom data
            ]
          ],
          'append' => true
        ];
      } else {
        return [
          'error' => 'Error uploading chunk ' . $_POST['chunkIndex']
        ];
      }
    }
    return [
      'error' => 'No file found'
    ];
  }


  function combineChunks($chunks, $targetFile)
  {
    // open target file handle
    $handle = fopen($targetFile, 'a+');

    foreach ($chunks as $file) {
      fwrite($handle, file_get_contents($file));
    }
    foreach ($chunks as $file) {
      @unlink($file);
    }
    // close the file handle
    fclose($handle);
  }

  public function saveVideoId($videoId, $filePath)
  {
    try {
      $data = [
        'video_id' => $videoId,
        'file_path' => $filePath,
        'created_at' => Carbon::now()->toDateTimeString()
      ];
      $videoUploadingRecordId = VideoUploading::insertGetId($data);
      $video_name = \explode('/', $filePath)[2] ?? 'default_video_name';
      $options = [
        'name' => $video_name,
        'description' => 'test video'
      ];
      UploadToVimeo::dispatch(public_path($filePath), $videoId, $videoUploadingRecordId, $options);
      return [
        'status' => true,
      ];
    } catch (\Exception $e) {
      return [
        'status' => false,
        'message' => $e->getMessage()
      ];
    }
  }

  public function processDeleteVideo($id)
  {
    try {
      $video = VideoUploading::where('id', $id)->first();
      dd($video);
      if ($video) {
        $clientId = env('VIMEO_CLIENT');
        $clientSecret = env('VIMEO_SECRET');
        $vimeoClient = new \Vimeo\Vimeo($clientId, $clientSecret);
        $vimeoClient->request("/videos/{$video->vimeo_id}", [], 'DELETE');
        DB::transaction();
        $video->delete();
        DB::commit();
      }
      return [
        'status' => true
      ];
    } catch (\Exception $e) {
      return [
        'status' => false,
        'message' => $e->getMessage()
      ];
    }
  }

  public function updateThumbnail()
  {
    $videoNullThumbnailData = VideoUploading::whereNotNull('vimeo_id')
      ->whereNull('thumbnail_id')
      ->orWhere('thumbnail_id', '')
      ->get()
      ->toArray();

    if (!empty($videoNullThumbnailData)) {
      foreach ($videoNullThumbnailData as $videoNullThumbnail) {
        $thumbnailApiLink = "/videos/" . $videoNullThumbnail['vimeo_id'] . "/pictures";
        $vimeoThumbnail = Vimeo::request($thumbnailApiLink, ['per_page' => 1], 'GET');

        // Kiểm tra xem có dữ liệu thumbnail không
        if (!empty($vimeoThumbnail['body']['data']) && count($vimeoThumbnail['body']['data']) > 0) {
          // Lấy URL thumbnail
          $thumbnailUrl = empty($vimeoThumbnail['body']['data'][0]['base_link'])
            ? $vimeoThumbnail['body']['data'][0]['link']
            : $vimeoThumbnail['body']['data'][0]['base_link'];

          // Cập nhật thumbnail cho video tương ứng
          VideoUploading::where('id', $videoNullThumbnail['id'])->update([
            'thumbnail_id' => $thumbnailUrl
          ]);
        }
      }
    }
  }

  public function formatVideoListDataTableForCreateCource($data)
  {
    return Datatables::of($data)
      ->addColumn('check', function ($row) {
        return '<input type="checkbox" class="form-checkbox-input">';
      })
      ->addIndexColumn()
      ->addColumn('title', function ($row) {
        return $row->video_id;
      })
      ->addColumn('videoThumbnail', function ($row) {
        if (!empty($row->thumbnail_id)) {
          return '<img style="width: 120px;height: 120px;" src="' . $row->thumbnail_id . '" />';
        }
        return '<img style="width: 120px;height: 120px;" src="' . url('/images/default_image.png') . '" />';
      })
      ->addColumn('created_at', function ($row) {
        return $row->created_at;
      })
      ->addColumn('action', function ($row) {
        return '<button type="button" class="btn btn-block btn-info btn-info-video" style=" width: 130px; " video-id="' . $row->vimeo_id . '">Xem video</button>';
      })
      ->rawColumns(['videoThumbnail', 'check', 'action'])
      ->make(true);
  }
}
