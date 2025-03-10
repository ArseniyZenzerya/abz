<?php

    namespace App\Http\Controllers;

    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Cache;
    use Illuminate\Support\Str;
    use Illuminate\Http\JsonResponse;

    class TokenController extends Controller
    {
        /**
         * @return JsonResponse
         */
        public function generateToken(): JsonResponse
        {
            $randomData = Str::random(100);
            $token = base64_encode($randomData);

            Cache::put("registration_token", $token, now()->addMinutes(40));

            return response()->json(["success" => true, "token" => $token]);
        }

        /**
         * @return bool
         */
        public static function invalidateToken(): bool
        {
            return Cache::forget("registration_token");
        }
    }
