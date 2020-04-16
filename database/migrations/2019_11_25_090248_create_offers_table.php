<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOffersTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('offers', function(Blueprint $table) {
			$table->bigIncrements('id');
			$table->unsignedBigInteger('request_r_id')->nullable();
			$table->foreign('request_r_id')->references('id')->on('request_r_s');
			$table->unsignedBigInteger('client_user_id')->nullable();
			$table->foreign('client_user_id')->references('id')->on('users');
			$table->unsignedBigInteger('performer_user_id')->nullable();
			$table->foreign('performer_user_id')->references('id')->on('users');
			$table->string('description', 1024)->nullable();
			$table->integer('price')->default(0);
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
		Schema::dropIfExists('offers');
	}
}
