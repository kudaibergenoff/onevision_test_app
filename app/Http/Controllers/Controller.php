<?php

namespace App\Http\Controllers;


/**
 * @OA\Info(
 *     title="OneVision Test App",
 *     version="1.0.0",
 *     description="OneVision Test Blog"
 * )
 * @OA\SecurityScheme(
 *     type="http",
 *     in="header",
 *     securityScheme="bearer",
 *     scheme="bearer"
 * )
 */
abstract class Controller
{
    public function sendResponse($result, $message): \Illuminate\Http\JsonResponse
    {
        $response = [
            'success' => true,
            'data'    => $result,
            'message' => $message,
        ];

        return response()->json($response, 200);
    }

    /**
     * return error response.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendError($error, $errorMessages = [], $code = 404): \Illuminate\Http\JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $error,
        ];

        if(!empty($errorMessages)){
            $response['data'] = $errorMessages;
        }

        return response()->json($response, $code);
    }
}
