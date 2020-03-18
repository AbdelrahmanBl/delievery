<?php

use Illuminate\Database\Seeder;
use App\User;
class userTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	DB::table('users')->truncate();
       	factory( User::class , 1000 )->create();
    }
}
