<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OptionsAnswerTypesTableSeeder extends Seeder
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
            'title' => 'Text Box',
            'description' => 'Explanations can be written in text box',
            'created_at' => DB::raw('CURRENT_TIMESTAMP'),
            'updated_at' => DB::raw('CURRENT_TIMESTAMP'),
        ));
        array_push($data, array(
            'id' => 2,
            'title' => 'File Upload',
            'description' => 'File Can be uploaded',
            'created_at' => DB::raw('CURRENT_TIMESTAMP'),
            'updated_at' => DB::raw('CURRENT_TIMESTAMP'),
        ));

        DB::table('cbt_option_answer_types')->truncate();
        DB::table('cbt_option_answer_types')->insert($data);

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
}
