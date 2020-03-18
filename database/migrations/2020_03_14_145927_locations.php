<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Locations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('locations', function (Blueprint $table) {
            $table->BigIncrements('id');
            $table->BigInteger('user_id');
            $table->string('name',30)->nullable();
            $table->string('address',60)->nullable();
            $table->double('latitude',15,8)->default(0);
            $table->double('longitude',15,8)->default(0);
            $table->string('type',15)->nullable();
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
        //
    }
}
