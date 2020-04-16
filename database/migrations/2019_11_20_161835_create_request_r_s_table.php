<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRequestRSTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('request_r_s', function(Blueprint $table) {
			$table->bigIncrements('id');
			$table->unsignedBigInteger('user_id');
			$table->foreign('user_id')->references('id')->on('users');
			$table->unsignedBigInteger('category_id');
			$table->foreign('category_id')->references('id')->on('categories');
			$table->unsignedBigInteger('location_id');
			$table->foreign('location_id')->references('id')->on('locations');
			$table->string('address')->nullable();
			$table->string('title');
			$table->string('description', 1024)->nullable();
			$table->integer('price')->default(0);
			$table->integer('status')->default(0);
			$table->datetime('date_start')->nullable();
			$table->datetime('date_end')->nullable();
			$table->unsignedBigInteger('performer_user_id')->nullable();
			$table->foreign('performer_user_id')->references('id')->on('users');
			$table->string('canceled_description')->nullable();
			$table->string('block_description')->nullable();
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
		Schema::dropIfExists('request_r_s');
	}
}
