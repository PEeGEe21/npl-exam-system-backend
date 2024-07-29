<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OptionTypesTableSeeder extends Seeder
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
            'title' => 'Multiple Choice Single Answer',
            'description' => 'An option type where you can choose on in the list of options',
            'created_at' => DB::raw('CURRENT_TIMESTAMP'),
            'updated_at' => DB::raw('CURRENT_TIMESTAMP'),
        ));
        array_push($data, array(
            'id' => 2,
            'title' => 'Multiple Selection',
            'description' => 'An option type where one can select multiple answer',
            'created_at' => DB::raw('CURRENT_TIMESTAMP'),
            'updated_at' => DB::raw('CURRENT_TIMESTAMP'),
        ));
        array_push($data, array(
            'id' => 3,
            'title' => 'Subjective Question',
            'description' => 'An option type where explanations are required as answer.',
            'created_at' => DB::raw('CURRENT_TIMESTAMP'),
            'updated_at' => DB::raw('CURRENT_TIMESTAMP'),
        ));
        array_push($data, array(
            'id' => 4,
            'title' => 'Comprehension Question',
            'description' => 'An option type where explanations are required as answer.',
            'created_at' => DB::raw('CURRENT_TIMESTAMP'),
            'updated_at' => DB::raw('CURRENT_TIMESTAMP'),
        ));
        array_push($data, array(
            'id' => 5,
            'title' => 'Theory Question',
            'description' => 'An option type where explanations are required as answer.',
            'created_at' => DB::raw('CURRENT_TIMESTAMP'),
            'updated_at' => DB::raw('CURRENT_TIMESTAMP'),
        ));

        DB::table('cbt_option_types')->truncate();
        DB::table('cbt_option_types')->insert($data);

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
}
