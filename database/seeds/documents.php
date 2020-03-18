<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;

class documents extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('documents')->truncate();
        DB::table('documents')->insert([
            [
                'name' => 'Driving Licence',
                'type' => 'DRIVER',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Card',
                'type' => 'DRIVER',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        ]);
        
    }
}
