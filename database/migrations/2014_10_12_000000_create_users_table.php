<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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
        Schema::create('user', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('email')->unique();
			$table->string('username');
			$table->string('firstname');
			$table->string('lastname');
			$table->string('password');
            $table->string('password_raw');
			$table->string('pin');
			$table->string('type');
            $table->smallInteger('status')->default(0);
            $table->smallInteger('streaming')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user');
    }
}
