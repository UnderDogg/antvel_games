<?php

/**
 * Antvel - Data Base
 * Categories Table.
 *
 * @author  Gustavo Ocanto <gustavoocanto@gmail.com>
 */
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('category_id')->unsigned()->nullable();
            $table->string('name', 50)->nullable();
            $table->string('description', 100)->nullable();
            $table->string('icon')->nullable();
            $table->string('image')->nullable();
            $table->boolean('status')->default(1);
            $table->enum('type', array_keys(trans('globals.type_categories')));

            $table->foreign('category_id')->references('id')->on('categories');

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
        Schema::drop('categories');
    }
}
