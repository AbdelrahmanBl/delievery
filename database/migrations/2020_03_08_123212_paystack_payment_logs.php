<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class PaystackPaymentLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('paystack_payment_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->enum('user_type',['USER','PROVIDER','UNKNOWN','PARTNER']);
            $table->enum('type',['RIDE','WALLET','UNKNOWN']);
            $table->integer('user_id')->nullable();
            $table->integer('provider_id')->nullable();
            $table->integer('user_request_id')->nullable();
            $table->string('paystack_id')->nullable();
            $table->string('customer_id')->nullable();
            $table->string('marketer_id')->nullable();
            $table->string('amount')->nullable();
            $table->string('reference')->nullable();
            $table->string('paid_at')->nullable();
            $table->string('payment_created_at')->nullable();
            $table->string('channel')->nullable();
            $table->string('currency')->nullable();
            $table->string('ip_address')->nullable();
            $table->longText('logs')->nullable();
            $table->longText('auth')->nullable();
            $table->longText('cust')->nullable();
            /*$table->json('logs')->nullable();
            $table->json('auth')->nullable();
            $table->json('cust')->nullable();*/
            $table->string('paidAt')->nullable();
            $table->string('payment_createdAt')->nullable();
            $table->string('transaction_date')->nullable();
            $table->string('fees')->nullable();
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
