<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ProviderDocuments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('provider_documents', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('provider_id');
            $table->string('document_id',64);
            $table->string('url',64);
            $table->string('unique_id',32)->nullable();
            $table->enum('status', ['ASSESSING', 'ACTIVE','ALERT','EXPIRED']);
            $table->timestamp('expires_at')->nullable();
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
