<?php

    namespace Database\Seeders;

    use Illuminate\Database\Seeder;
    use App\Models\Position;

    class PositionSeeder extends Seeder
    {
        public function run()
        {
            $positions = [
                ['id' => 1, 'name' => 'Lawyer'],
                ['id' => 2, 'name' => 'Content manager'],
                ['id' => 3, 'name' => 'Security'],
                ['id' => 4, 'name' => 'Designer'],
            ];

            foreach ($positions as $position) {
                Position::updateOrCreate(['id' => $position['id']], $position);
            }
        }
    }
