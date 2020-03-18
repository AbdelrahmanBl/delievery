<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ServicesTypes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('service_types', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name',12);
            $table->string('provider_name',30)->nullable();
            $table->string('image',100)->nullable();
            $table->decimal('capacity',1,0)->default(0);
            $table->decimal('fixed',2,0);
            //$table->decimal('price',3,0);
            $table->decimal('minute',2,0);
            //$table->decimal('hour',2,0)->nullable();
            $table->decimal('distance',1,0);
            $table->string('address')->nullable();
            $table->double('latitude',15,8)->default(0);
            $table->double('longitude',15,8)->default(0);

            $table->enum('calculator', ['MIN', 'HOUR', 'DISTANCE', 'DISTANCEMIN', 'DISTANCEHOUR']);
            $table->string('description',50)->nullable();
            $table->decimal('status',1,0)->default(1);
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
