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
			$table->decimal('money', 25, 2)->default(0.00);
			$table->decimal('max_bet', 25, 2)->default(0.00);
			$table->decimal('max_draw_bet', 25, 2)->default(5000.00);
			$table->smallInteger('board_access')->default(0);
			$table->string('allowed_sides')->default('n');
			$table->string('lang_code')->default('en');
			$table->bigInteger('ui_code');
			$table->bigInteger('group_id');
			$table->string('password');
            $table->string('password_raw');
			$table->string('pin');
			$table->string('type');
            $table->smallInteger('status')->default(0);
            $table->smallInteger('streaming')->default(1);
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
