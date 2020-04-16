<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserSecretsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('user_secrets', function(Blueprint $table) {
			$table->bigIncrements('id');
			$table->unsignedBigInteger('user_id');
			$table->foreign('user_id')->references('id')->on('users');
			$table->integer('balance')->default(0);
			$table->string('iin')->nullable();
			$table->string('nation')->nullable();
			$table->string('idCardNumber')->nullable();
			$table->string('placeOfBirth')->nullable();
			$table->string('authority')->nullable();
			$table->date('idCardIssueDate')->nullable();
			$table->date('idCardExpireDate')->nullable();
			$table->text('firebase_fcm_token')->nullable();
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
		Schema::dropIfExists('user_secrets');
	}
}
