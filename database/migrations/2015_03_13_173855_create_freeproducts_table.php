<?php

/**
 * Antvel - Data Base
 * Free Products Table.
 *
 * @author  Gustavo Ocanto <gustavoocanto@gmail.com>
 */
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateFreeproductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('freeproducts', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned()->index()->nullable();
            $table->string('description')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->integer('participation_cost')->nullable();
            $table->integer('min_participants')->nullable();
            $table->integer('max_participants')->nullable();
            $table->integer('max_participations_per_user')->nullable();
            $table->integer('draw_number')->nullable();
            $table->date('draw_date')->nullable();
            $table->boolean('status')->default(1);
            $table->foreign('user_id')->references('id')->on('users');
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
        Schema::drop('freeproducts');
    }
}
