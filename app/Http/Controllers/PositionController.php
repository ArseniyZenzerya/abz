<?php

    namespace App\Http\Controllers;

    use App\Models\Position;
    use Illuminate\Http\JsonResponse;
    use App\Traits\ApiResponseTrait;

    class PositionController extends Controller
    {
        use ApiResponseTrait;

        /**
         * Retrieve all positions.
         *
         * @return JsonResponse
         */
        public function getPositions(): JsonResponse
        {
            $positions = Position::all();

            if ($positions->isEmpty()) {
                return $this->errorResponse('Positions not found', 404);
            }

            return $this->successResponse(['positions' => $this->formatPositions($positions)]);
        }

        /**
         * Format positions.
         *
         * @param \Illuminate\Support\Collection $positions
         * @return array
         */
        private function formatPositions($positions): array
        {
            return $positions->map(fn(Position $position) => [
                'id' => $position->id,
                'name' => $position->name,
            ])->toArray();
        }
    }
