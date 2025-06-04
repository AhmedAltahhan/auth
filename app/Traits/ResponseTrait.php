<?php

namespace App\Traits;

trait ResponseTrait
{
    public function successResponse($data,$message,$code)
    {
        return response()->json(['status' => true,'data' => $data, 'message' => $message],$code);
    }

    public function errorResponse($data,$message,$code)
    {
        return response()->json(['status' => false,'data' => $data,'message' => $message],$code);
    }

    public function messageSuccessResponse($message,$code)
    {
        return response()->json(['status' => true,'message' => $message],$code);
    }

    public function messageErrorResponse($message,$code = 400)
    {
        return response()->json(['status' => false,'message' => $message],$code);
    }
}
