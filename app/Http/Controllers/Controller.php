<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Response;
use App\Helpers\ResponseCode;

class Controller extends BaseController
{
    use AuthorizesRequests;
    use DispatchesJobs;
    use ValidatesRequests;
    public function successResponse($data = null)
    {
        $response = ['message' => ResponseCode::$SUCCESS];
        if ($data) {
            $response = ['message' => ResponseCode::$SUCCESS, 'data' => $data];
        }
        return response()->json($response, Response::HTTP_OK);
    }
    public function notFoundErrorResponse()
    {
        return response()->json([
            'errorCode' => ResponseCode::$NOT_FOUND,
            'message' => __('error')[ResponseCode::$NOT_FOUND]
        ], Response::HTTP_NOT_FOUND);
    }
    public function internalServerErrorResponse()
    {
        return response()->json([
            'errorCode' => ResponseCode::$INTERNAL_ERROR,
            'message' => __('error')[ResponseCode::$INTERNAL_ERROR]
        ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
    public function createdSuccessResponse()
    {
        return response()->json([
            'message' => ResponseCode::$CREATED,
        ], Response::HTTP_OK);
    }
    public function deletedSuccessResponse()
    {
        return response()->json([
            'message' => ResponseCode::$DELETED,
        ], Response::HTTP_OK);
    }
}
