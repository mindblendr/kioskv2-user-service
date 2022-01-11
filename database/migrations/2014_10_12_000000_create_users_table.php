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
            $table->string('email')->nullable();
			$table->string('username');
			$table->string('firstname');
			$table->string('lastname');
			$table->string('phone');
			$table->decimal('money', 25, 2);
			$table->string('lang_code');
			$table->bigInteger('ui_code');
			$table->bigInteger('group_id');
			$table->string('phone');
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
