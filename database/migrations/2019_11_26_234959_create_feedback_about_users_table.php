<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFeedbackAboutUsersTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('feedback_about_users', function(Blueprint $table) {
			$table->bigIncrements('id');
			$table->unsignedBigInteger('client_user_id');
			$table->foreign('client_user_id')->references('id')->on('users');
			$table->unsignedBigInteger('performer_user_id');
			$table->foreign('performer_user_id')->references('id')->on('users');
			$table->unsignedBigInteger('request_r_id')->nullable();
			$table->foreign('request_r_id')->references('id')->on('request_r_s');
			$table->string('message', 512)->nullable();
			$table->integer('rating')->default(0);
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
		Schema::dropIfExists('feedback_about_users');
	}
}
