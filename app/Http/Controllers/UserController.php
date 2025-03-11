<?php

    namespace App\Http\Controllers;

    use App\Http\Requests\GetUsersRequest;
    use App\Http\Requests\RegisterUserRequest;
    use App\Models\User;
    use App\Services\ImageProcessingService;
    use App\Traits\ApiResponseTrait;
    use Illuminate\Http\JsonResponse;
    use Illuminate\Pagination\LengthAwarePaginator;
    use Illuminate\Support\Facades\Cache;

    class UserController extends Controller
    {
        use ApiResponseTrait;

        protected $imageProcessingService;

        public function __construct(ImageProcessingService $imageProcessingService)
        {
            $this->imageProcessingService = $imageProcessingService;
        }

        /**
         * Register a new user.
         *
         * @param RegisterUserRequest $request
         * @return JsonResponse
         */
        public function register(RegisterUserRequest $request): JsonResponse
        {
            try {
                $photoPath = $this->imageProcessingService->storeAndOptimizePhoto($request);

                if (!$photoPath) {
                    return $this->errorResponse('Failed to upload and optimize photo.', 500);
                }

                $user = User::create(array_merge($request->validated(), ['photo' => $photoPath]));

                if (!$user) {
                    return $this->errorResponse('Failed to create user.', 500);
                }

                Cache::forget('registration_token');
                return $this->successResponse(['user' => $user], 201);
            } catch (\Exception $e) {
                return $this->errorResponse('An error occurred while processing the request.', 500, $e);
            }
        }

        /**
         * Get users with pagination.
         *
         * @param GetUsersRequest $request
         * @return JsonResponse
         */
        public function getUsers(GetUsersRequest $request): JsonResponse
        {
            $data = $request->validatedWithDefaults();
            $users = $this->paginateUsers($data['count'], $data['page']);

            return $this->successResponse([
                'page' => $users->currentPage(),
                'total_pages' => $users->lastPage(),
                'total_users' => $users->total(),
                'count' => $data['count'],
                'links' => [
                    'next_url' => $users->nextPageUrl(),
                    'prev_url' => $users->previousPageUrl()
                ],
                'users' => $this->formatUsers($users)
            ]);
        }

        /**
         * Get user by ID.
         *
         * @param int $id
         * @return JsonResponse
         */
        public function getUserById(int $id): JsonResponse
        {
            if ($id <= 0) {
                return $this->errorResponse('The user ID must be an integer.', 400);
            }

            $user = User::with('position')->find($id);

            if (!$user) {
                return $this->errorResponse('User not found', 404);
            }

            return $this->successResponse(['user' => $this->formatUser($user)]);
        }

        /**
         * Paginate users.
         *
         * @param int $count
         * @param int $page
         * @return LengthAwarePaginator
         */
        private function paginateUsers(int $count, int $page): LengthAwarePaginator
        {
            return User::with('position')->orderBy('id', 'asc')->paginate($count, ['*'], 'page', $page);
        }

        /**
         * Format user data.
         *
         * @param User $user
         * @return array
         */
        private function formatUser(User $user): array
        {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'position' => $user->position->name ?? null,
                'position_id' => $user->position_id,
                'registration_timestamp' => $user->created_at->timestamp,
                'photo' => asset('storage/' . $user->photo),
            ];
        }

        /**
         * Format multiple users.
         *
         * @param LengthAwarePaginator $users
         * @return array
         */
        private function formatUsers(LengthAwarePaginator $users): array
        {
            return $users->getCollection()->map(fn($user) => $this->formatUser($user))->toArray();
        }
    }
