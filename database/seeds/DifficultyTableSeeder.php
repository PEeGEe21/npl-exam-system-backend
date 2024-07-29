<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DifficultyTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        $data = array();

        array_push($data, array(
            'id' => 1,
            'title' => 'EASY',
            'description' => NULL,
            'created_at' => DB::raw('CURRENT_TIMESTAMP'),
            'updated_at' => DB::raw('CURRENT_TIMESTAMP'),
        ));
        array_push($data, array(
            'id' => 2,
            'title' => 'MEDIUM',
            'description' => NULL,
            'created_at' => DB::raw('CURRENT_TIMESTAMP'),
            'updated_at' => DB::raw('CURRENT_TIMESTAMP'),
        ));

        array_push($data, array(
            'id' => 3,
            'title' => 'INTERMEDIATE',
            'description' => NULL,
            'created_at' => DB::raw('CURRENT_TIMESTAMP'),
            'updated_at' => DB::raw('CURRENT_TIMESTAMP'),
        ));
        array_push($data, array(
            'id' => 4,
            'title' => 'ADVANCED',
            'description' => NULL,
            'created_at' => DB::raw('CURRENT_TIMESTAMP'),
            'updated_at' => DB::raw('CURRENT_TIMESTAMP'),
        ));
        array_push($data, array(
            'id' => 5,
            'title' => 'HARD',
            'description' => NULL,
            'created_at' => DB::raw('CURRENT_TIMESTAMP'),
            'updated_at' => DB::raw('CURRENT_TIMESTAMP'),
        ));

        DB::table('cbt_difficulties')->truncate();
        DB::table('cbt_difficulties')->insert($data);

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
}
