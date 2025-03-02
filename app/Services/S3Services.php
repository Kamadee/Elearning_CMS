<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class S3Services
{
  public function uploadToS3($file, $folder)
  {
    $name = time() . $file->getClientOriginalName();
    $filePath = $folder . '/' . $name;
    $path = Storage::disk('s3')->put($filePath, file_get_contents($file), 'public');

    if (Storage::disk('s3')->exists($filePath)) {
      return Storage::disk('s3')->url($filePath);
    }
    return 'empty';
  }

  public function destroy($fullUrl)
  {
    $filePath = parse_url($fullUrl, PHP_URL_PATH);
    Storage::disk('s3')->delete(ltrim($filePath, '/'));
    return;
  }
}
