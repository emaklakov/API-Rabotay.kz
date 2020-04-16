<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotificationsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('notifications', function(Blueprint $table) {
			$table->bigIncrements('id');
			$table->unsignedBigInteger('user_id');
			$table->foreign('user_id')->references('id')->on('users');
			$table->string('title');
			$table->string('description_min')->nullable();
			$table->string('description', 1024)->nullable();
			$table->string('action_title')->nullable();
			$table->string('action_route')->nullable();
			$table->string('icon')->default('notifications');
			$table->integer('status')->default(0);
			$table->boolean('is_send')->default(false);
			$table->boolean('is_received')->default(false);
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
		Schema::dropIfExists('notifications');
	}
}
