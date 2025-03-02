<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Redis;
use Vimeo\Laravel\Facades\Vimeo;
use App\Models\VideoUploading;
use Carbon\Carbon;
use Helper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UploadToVimeo implements ShouldQueue
{
  use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

  protected $file;
  protected $options;
  protected $localfileId;
  protected $videoUploadingRecordId;
  /**
   * Create a new job instance.
   *
   * @return void
   */
  public function __construct($file, $localfileId, $videoUploadingRecordId, $options = [])
  {
    $this->file = $file;
    $this->localfileId = $localfileId;
    $this->videoUploadingRecordId = $videoUploadingRecordId;
    $this->options = $options;
  }

  /**
   * Execute the job.
   *
   * @return void
   */
  public function handle()
  {
    DB::beginTransaction();
    try {
      VideoUploading::where('id', $this->videoUploadingRecordId)->update(['job_id' => $this->job->getJobId()]);
      $vimeoVideoId = Vimeo::upload($this->file, $this->options);
      var_dump($vimeoVideoId);
      $vimeoCode = basename($vimeoVideoId);
      var_dump($vimeoCode);
      VideoUploading::where('id', $this->videoUploadingRecordId)
        ->update([
          'vimeo_id' => $vimeoCode,
          'file_path' => null,
          'updated_at' => Carbon::now()->toDateTimeString(),
          'job_status' => config('constants.job_status.success')
        ]);
      DB::commit();
      // unlink($this->file);
    } catch (\Exception $e) {
      var_dump(333, $e);
      DB::rollBack();
      DB::beginTransaction();
      VideoUploading::where('id', $this->videoUploadingRecordId)
        ->update([
          'vimeo_id' => null,
          'job_status' => config('constants.job_status.fail'),
          'error_log' => $e->getMessage()
        ]);
      DB::commit();
    }
  }
}
