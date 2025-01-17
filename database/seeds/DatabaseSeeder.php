<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        // $this->call(UserSeeder::class);
        $this->call(DifficultyTableSeeder::class);
        $this->call(OptionTypesTableSeeder::class);
        $this->call(OptionsAnswerTypesTableSeeder::class);
    }
}
