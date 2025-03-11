<?php

    namespace Database\Seeders;

    use Illuminate\Database\Seeder;
    use App\Models\User;
    use Faker\Factory as Faker;

    class UserSeeder extends Seeder
    {
        public function run()
        {
            $faker = Faker::create();
            $positions = [1, 2, 3, 4];
            $defaultPhoto = 'images/default.jpg';

            for ($i = 0; $i < 45; $i++) {
                $name = $faker->name();
                $email = $faker->unique()->safeEmail();
                $phone = '+380' . $faker->numerify('#########');
                $position_id = $faker->randomElement($positions);

                User::create([
                    'name' => $name,
                    'email' => $email,
                    'phone' => $phone,
                    'position_id' => $position_id,
                    'photo' => $defaultPhoto,
                ]);
            }
        }
    }
