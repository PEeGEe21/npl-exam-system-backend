<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCbtTestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cbt_tests', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->double('mark_per_question')->default(0);
            $table->double('total_questions')->default(0);
            $table->double('total_marks')->default(0);
            $table->string('title');
            $table->string('code');
            $table->unsignedInteger('result_published')->default(0);
            $table->unsignedInteger('shuffle');
            $table->integer('shuffle_answer')->default(0);
            $table->unsignedInteger('view_correct_answer');
            $table->unsignedInteger('auto_publish');
            $table->unsignedInteger('is_published')->default(0);
            $table->unsignedInteger('duration_hours');
            $table->unsignedInteger('duration_minutes');
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->unsignedInteger('type')->default(1);
            $table->longText('instructions')->nullable();
            $table->integer('is_random_questions')->default(0);
            $table->integer('max_no_question')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cbt_tests');
    }
}
