<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UserRequests extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_requests', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('booking_id',64)->unique();
            $table->bigInteger('user_id');
            $table->bigInteger('provider_id')->nullable();
            $table->bigInteger('current_provider_id')->nullable();
            $table->bigInteger('service_type_id')->nullable();
            $table->bigInteger('promocode_id')->nullable();
            $table->decimal('rental_hours',2,0)->nullable();/*       Not Found    */
            $table->enum('status', ['SEARCHING','CANCELLED','ACCEPTED','STARTED','ARRIVED','PICKEDUP','DROPPED','COMPLETED','SCHEDULED']);/*       Length Edited  : varchar(255)    */
            $table->enum('cancelled_by', ['NONE','USER','PROVIDER'])->comment('NONE,USER,PROVIDER');
            $table->enum('cancel_reason',['NO_SHOW', 'CHANGE_MIND', 'LATE', 'ISFAR', 'OTHER'])->default('OTHER')->comment('NO_SHOW, CHANGE_MIND, LATE, ISFAR, OTHER');
            $table->bigInteger('subscriptionid')->unique()->nullable();
            $table->string('payment_mode',10)->default('CASH')->comment('CASH,VISA,MADA,SUBSCRIBED,WALLET');
            $table->tinyInteger('paid')->default(0)->comment('0: Not Paid, 1:Paid');
            $table->enum('is_track', ['YES','NO'])->default('NO')->comment('YES,NO');
            $table->enum('is_best_price', ['YES','NO'])->default('NO')->comment('YES,NO');
            $table->double('distance',18,8)->default(0);
            $table->decimal('travel_time',4,0);/*       Not Found    */
            $table->enum('unit',['KM','Miles'])->default('KM')->comment('KM,Miles');
            $table->string('s_address')->nullable();
            $table->double('s_latitude',15,8)->default(0);
            $table->double('s_longitude',15,8)->default(0);
            $table->string('d_address')->nullable();
            //$table->tinyInteger('otp')->length(4)->nullable();
            $table->double('d_latitude',15,8)->default(0);
            $table->double('d_longitude',15,8)->default(0);
            $table->longText('destination_points')->nullable();
            $table->double('track_distance',15,8)->default(0);
            $table->double('track_latitude',15,8)->default(0);
            $table->double('track_longitude',15,8)->default(0);
            $table->timestamp('assigned_at')->useCurrent();
            $table->timestamp('schedule_at')->nullable();
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('finished_at')->useCurrent();
            $table->enum('is_scheduled',['YES', 'NO'])->default('NO')->comment('YES,NO');
            $table->tinyInteger('user_rated')->default(0);
            $table->tinyInteger('provider_rated')->default(0);
            $table->tinyInteger('use_wallet')->default(0);
            $table->tinyInteger('surge')->default(0);
            $table->longText('route_key')->nullable();/*       Not Found    */
            $table->timestamp('deleted_at')->useCurrent();
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
