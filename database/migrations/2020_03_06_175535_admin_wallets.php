<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AdminWallets extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admin_wallets', function (Blueprint $table) {
            $table->increments('id');           
            $table->string('transaction_id',15);
            $table->string('transaction_alias')->nullable();
            $table->string('transaction_desc')->nullable();
            $table->integer('transaction_type')->nullable()->comment('1-commission,2-userrecharge,3-tripdebit,4-providerrecharge,5-providersettle,6-fleetrecharge,7-fleetcommission,8-fleetsettle,9-taxcredit,10-discountdebit,11-discountrecharge');
            $table->enum('type', [
                    'C',
                    'D',
                ]);
            $table->double('amount', 15, 8)->default(0);
            $table->double('open_balance', 15, 8)->default(0);
            $table->double('close_balance', 15, 8)->default(0);
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
