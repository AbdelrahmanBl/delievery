<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Providers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('providers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('first_name',15);
            $table->string('last_name',15);/*       Length Edited  : 16    */
            $table->string('email',64)->nullable()->unique();
            $table->enum('gender',['M','F','U'])->default('U')->comment('M: Male, F:Female, U:Unspecified');
            $table->string('mobile',20)->unique();/*       Length Edited  : 255    */
            $table->string('password',64);
            $table->string('avatar',64)->nullable()->comment('Image name without path');
            $table->decimal('rating',4,2)->default(5);
            $table->enum('status',['document','card','onboarding','approved','banned'])->default('document');
            $table->enum('provider_status',['Online','Offline','Busy'])->default('Offline');
            $table->decimal('fleet',11,0)->default(0); 
            $table->double('latitude', 15, 8)->default(0);
            $table->double('longitude', 15, 8)->default(0);
            $table->string('stripe_acc_id',100)->nullable()->unique();/*       Not Found    */
            $table->string('stripe_cust_id',100)->nullable()->unique();/*       Not Found    */
            $table->double('wallet_balance',8,2)->default(0);/*       Length Edited  : varchar(255)    */
            $table->string('social_unique_id')->nullable();
            //$table->enum('login_by', ['manual','facebook','google'])->default('manual')->comment('manual,facebook,google');
            $table->enum('language',['ar','en'])->default('ar')->comment('ar: Arabic, en:English');
            $table->string('currency',4)->default('EG');
            $table->string('country_code',4)->default('002');
            $table->decimal('limits',4,0)->default(500);
            $table->enum('device_type',['android', 'ios', 'Web'])->default('Web')->comment('android, ios, Web');
            /*       ADDED     */
            $table->string('city_code',4)->nullable();
            $table->string('district',30)->nullable();
            /*    -----        */
            $table->timestamp('last_login')->useCurrent();
            $table->rememberToken();
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
