<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateCbtResultsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cbt_results', function (Blueprint $table) {
            $table->increments('id');
            $table->string('student_id');
            $table->unsignedInteger('test_id');
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->timestamp('server_end_date')->nullable();
            $table->unsignedInteger('duration')->nullable();
            $table->double('total_scored')->nullable();
            $table->integer('manual_lock')->default(0);
            $table->unsignedInteger('total_count')->nullable();
            $table->unsignedInteger('total_marks')->nullable();
            $table->string('question_test_ids',5000)->nullable();
            $table->timestamps();
        });
        $url = "CREATE INDEX  index_cbt_result on `cbt_results` (`test_id`,`student_id`,`end_date`)";
        DB::statement($url);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cbt_results');
    }
}
