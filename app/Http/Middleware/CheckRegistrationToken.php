<?php

    namespace App\Http\Middleware;

    use Closure;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Cache;

    class CheckRegistrationToken
    {
        public function handle(Request $request, Closure $next)
        {
            $tokenFromHeader = $request->bearerToken();

            if (!$tokenFromHeader) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token is required.',
                ], 400);
            }

            $cachedToken = Cache::get('registration_token');

            if ($tokenFromHeader !== $cachedToken) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or expired token.',
                ], 403);
            }

            return $next($request);
        }
    }
