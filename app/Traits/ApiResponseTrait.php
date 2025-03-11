<?php

    namespace App\Traits;

    use Illuminate\Http\JsonResponse;

    trait ApiResponseTrait
    {
        /**
         * Generate a success response.
         *
         * @param array $data
         * @param int $status
         * @return JsonResponse
         */
        protected function successResponse(array $data, int $status = 200): JsonResponse
        {
            return response()->json(array_merge(['success' => true], $data), $status);
        }

        /**
         * Generate an error response.
         *
         * @param string $message
         * @param int $status
         * @param \Exception|null $exception
         * @return JsonResponse
         */
        protected function errorResponse(string $message, int $status = 500, ?\Exception $exception = null): JsonResponse
        {
            $response = ['success' => false, 'message' => $message];
            if ($exception) {
                $response['error'] = $exception->getMessage();
            }
            return response()->json($response, $status);
        }
    }
