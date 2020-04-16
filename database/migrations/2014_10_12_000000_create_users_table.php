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
		Schema::create('users', function(Blueprint $table) {
			$table->bigIncrements('id');
			$table->string('first_name')->nullable();
			$table->string('last_name')->nullable();
			$table->string('middle_name')->nullable();
			$table->string('phone', 12)->unique(); // 77024472944 - 11 символов
			$table->index('phone');
			$table->string('firebase_user_uid')->nullable();
			$table->integer('status')->default(10); // ACTIVE = 10; BLOCKED = 0; OWES = 20
			$table->rememberToken();
			$table->timestamps();
			$table->softDeletes();
		});

		DB::statement('ALTER TABLE users AUTO_INCREMENT = 2010000;');
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
