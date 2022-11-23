<?php

namespace App\Transformer;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;

class ApiResponseTransformer
{
    public static function success($data, $responseMessage, $statusCode = 200): JsonResponse
    {
        $response = array(
            'success' => true,
            'message' => $responseMessage,
        );

        if (isset($data)) {
            if ($data instanceof LengthAwarePaginator) {
                $response = array_merge(
                    $response,
                    array(
                        "data" => $data->all(),
                        "pagination" => self::pagination($data)
                    )
                );
            } elseif ($data instanceof ResourceCollection) {
                $data = json_decode($data->toJson(), true);
                if (isset($data['data']) && isset($data['pagination'])) {
                    $response = array_merge(
                        $response,
                        array(
                            "data" => $data['data'],
                            "pagination" => $data ['pagination']
                        )
                    );
                } else {
                    $response = array_merge(
                        $response,
                        array(
                            "data" => $data
                        )
                    );
                }
            } elseif ($data instanceof Model) {
                $response = array_merge(
                    $response,
                    array(
                        "data" => $data->toArray()
                    )
                );
            } else {
                $response = array_merge(
                    $response,
                    array(
                        "data" => $data
                    )
                );
            }
        }

        return new JsonResponse($response, $statusCode);
    }

    public static function pagination($data): array
    {
        return array(
            'total' => $data->total(),
            'current_items_count' => $data->count(),
            'items_per_page' => $data->perPage(),
            'current_page_no' => $data->currentPage(),
            'last_page_no' => $data->lastPage(),
            'has_more_pages' => $data->hasMorePages(),
        );
    }

    /**
     * @param $errors
     * @param $message
     * @param $statusCode
     * @return JsonResponse
     */
    public static function error($errors, $message, $statusCode): JsonResponse
    {
        $response = array(
            'success' => false,
            'message' => $message,
            "errors" => $errors
        );
        return new JsonResponse($response, $statusCode);
    }
}
