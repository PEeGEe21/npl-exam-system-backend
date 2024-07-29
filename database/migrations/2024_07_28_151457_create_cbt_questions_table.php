<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateCbtQuestionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cbt_questions', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('difficulty_id')->default(1);
            $table->unsignedInteger('option_type_id')->default(1);
            $table->unsignedInteger('is_editor')->default(0);
            $table->longText('question');
            $table->longText('question_plain')->nullable();
            $table->text('tags')->nullable();
            $table->double('marks')->default(0);
            $table->longText('instruction')->nullable();
            $table->unsignedInteger('option_answer_type_id')->default(1);
            $table->boolean('is_exam')->default(false);
            $table->timestamps();
        });
        $url = "CREATE INDEX  index_cbt_question on `cbt_questions` (`user_id`)";
        DB::statement($url);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cbt_questions');
    }
}
