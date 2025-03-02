<?php

namespace App\Helpers;

use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Ramsey\Uuid\Ulid;

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

    public static function createLogInfo($info)
    {
        $logger = new Logger('CUSTOM_LOGS');
        $logger->pushHandler(
            new RotatingFileHandler(storage_path('logs/custom_logs_api.log'), config('app.log_max_files', 0))
        );
        if (!is_string($info)) {
            $info = json_encode($info);
        }
        $logger->info($info);
    }

    function getConstant($name, $default = null) // Hàm chọc vào constants trong config
    {
        if (empty($name)) return null;
        return config("constants.$name") ?? $default;
    }

    public static function escapeLike($str)
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], $str);
    }

    public static function renderSortData($request, $conditionData, $sortColumn)
    {
        if (!$request->has('sort') || empty($request->sort)) {
            return $conditionData;
        }
        // Biến sort từ json về mảng php
        $request->merge(['sort' => array_map(
            function ($a) {
                return json_decode($a, true);
            },
            $request->sort
        )]);

        foreach ($request->sort as $sortData) {
            if (
                !is_array($sortData) || empty($sortData['field']) || empty($sortData['type'])
                || !in_array($sortData['field'], $sortColumn)
                || !in_array(strtoupper($sortData['type']), getConstant('SORT_ORDER'))
            ) {
                continue;
            }
            $conditionData->orderBy($sortData['field'], $sortData['type']);
        }
        return $conditionData;
    }

    public static function generateCodeUlid($prefix, $index)
    {
        $ulid = Ulid::generate()->getTime();
        $code = $prefix . $ulid . $index;

        return $code;
    }

    public static function checkPermission($permissionGuardName)
    {
        $userId = Auth::user()->id;
        return User::find($userId)->hasPermissionTo($permissionGuardName);
    }

    public static function convertMoney($number, $ext = ' ₫')
    {
        return number_format($number, 0, '', ',') . $ext;
    }
}
function getConstant($name, $default = null)
{
    if (empty($name)) return null;
    return config("constants.$name") ?? $default;
}
