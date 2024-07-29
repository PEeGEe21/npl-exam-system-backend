<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateCbtResultScoresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!Schema::hasTable("cbt_result_scores")){
            Schema::create('cbt_result_scores', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('result_id');
                $table->unsignedInteger('question_id');
                $table->string('score')->default('0');
                $table->boolean('scored')->default(true);
                $table->json('answer')->nullable();
                $table->unsignedInteger('state')->nullable();
                $table->longText('comments')->nullable();
                $table->integer('question_test_id')->nullable();
                $table->decimal('time',30,2)->nullable();
                $table->longText('student_answer')->nullable();
                $table->boolean('is_correct')->default(false);
                $table->longText('correct_answer')->nullable();
                $table->index(['result_id','question_id','question_test_id'],
                    'index_cbt_result_score');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cbt_result_scores');
    }
}
