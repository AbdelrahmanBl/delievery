<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Complaints extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('complaints', function (Blueprint $table) {
            $table->BigIncrements('id');
            $table->BigInteger('user_id');
            $table->BigInteger('provider_id');
            $table->string('complaint',100);
            $table->string('reply',100)->nullable();
            $table->enum('sender',['USER','PROVIDER']);
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
