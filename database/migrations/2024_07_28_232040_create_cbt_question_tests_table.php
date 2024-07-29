<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateCbtQuestionTestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!Schema::hasTable('cbt_question_tests')) {
            Schema::create('cbt_question_tests', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('question_id');
                $table->unsignedInteger('test_id');
                $table->unsignedInteger('position_id')->nullable();
                $table->unsignedInteger('user_id')->nullable();
                $table->unsignedInteger('difficulty_id')->default(1);
                $table->unsignedInteger('topic_id')->nullable();
                $table->unsignedInteger('option_answer_type_id')->nullable();
                $table->unsignedInteger('option_type_id')->default(1);
                $table->unsignedInteger('is_editor')->default(0);
                $table->unsignedInteger('is_exam')->default(0);
                $table->longText('question')->nullable();
                $table->longText('question_plain')->nullable();
                $table->text('tags')->nullable();
                $table->longText('instruction')->nullable();
                $table->double('mark')->default(0);
                $table->timestamps();
            });
            $url = "CREATE INDEX  index_cbt_question_test on `cbt_question_tests` (`test_id`,`question_id`)";
            DB::statement($url);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cbt_question_tests');
    }
}
