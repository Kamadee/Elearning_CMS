<?php

namespace App\Helpers;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;

class Helper
{
  public static function createLogError($error)
  {
      $logger = new Logger('CUSTOM_LOGS');
      $logger->pushHandler(
          new RotatingFileHandler(storage_path('logs/custom_logs_api.log'), config('app.log_max_files', 0))
      );
      if (!is_string($error)) {
          $error = json_encode([$error]);
      }
      $logger->error($error);
  }

}
