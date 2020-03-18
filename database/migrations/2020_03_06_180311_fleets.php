<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Fleets extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fleets', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('email');
            $table->string('password');
            $table->string('company')->nullable();
            $table->string('mobile')->nullable();
            $table->string('logo')->nullable();
            $table->rememberToken();
            $table->double('commission', 5,2)->default(0);
            $table->double('wallet_balance', 10,2)->default(0);
            $table->string('stripe_cust_id')->nullable();
            $table->string('language',2)->nullable();
            $table->string('currency',4)->default('EG');
            $table->decimal('limits',4,0)->default(500);
            $table->string('country_code',4)->default('002');
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
