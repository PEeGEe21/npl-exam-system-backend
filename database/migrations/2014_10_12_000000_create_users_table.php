<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('full_name')->nullable();
            $table->string('email')->unique();
            $table->string('password');
            $table->integer('status')->default(1);
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('last_login')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->integer('login_status')->default(0);
            $table->integer('forced_logout')->default(0);
            $table->integer('logout_timestamp')->default(0);
            $table->rememberToken();
            $table->softDeletes();
            $table->timestamps();
        });

        $url = "CREATE INDEX  index_user on `users` (`full_name`,`status`,`deleted_at`)";
        DB::statement($url);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
