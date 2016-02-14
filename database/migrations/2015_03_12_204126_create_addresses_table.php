<?php

/**
 * Antvel - Data Base
 * Addresses Table.
 *
 * @author  Gustavo Ocanto <gustavoocanto@gmail.com>
 */
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAddressesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('addresses', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned()->nullable();
            $table->boolean('default')->default(1);
            $table->string('line1')->nullable();
            $table->string('line2')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('name_contact', 100)->nullable();
            $table->string('zipcode')->nullable();
            $table->string('city')->nullable();
            $table->string('country', 50)->nullable();
            $table->string('state')->nullable();
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users');
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
        Schema::drop('addresses');
    }
}
