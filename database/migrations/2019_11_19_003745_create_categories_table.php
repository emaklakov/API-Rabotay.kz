<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatecategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->bigIncrements('id');
			$table->string('name');
			$table->string('slug');
			$table->string('icon')->nullable();
			$table->integer('sort')->default(50);
			$table->integer('count_requests')->default(00);
			$table->boolean('is_disabled')->default(true);
			$table->nestedSet();
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
        Schema::dropIfExists('сategories');
    }
}
