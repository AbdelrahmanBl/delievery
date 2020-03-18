<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UserSubscriptionHistories extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_subscription_histories', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->nullable(); 
            $table->integer('package_id')->nullable(); 
            $table->string('payment_id')->nullable();
            $table->string('card_id')->nullable();  
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
