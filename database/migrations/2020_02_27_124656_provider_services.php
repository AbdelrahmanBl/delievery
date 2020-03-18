<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ProviderServices extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('provider_services', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('provider_id');
            $table->bigInteger('service_type_id');
            $table->enum('status', ['active', 'offline','riding']);
            $table->string('service_number',20)->nullable();
            $table->string('service_model',20)->nullable();
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
