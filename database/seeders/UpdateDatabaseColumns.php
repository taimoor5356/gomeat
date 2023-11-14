<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UpdateDatabaseColumns extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Add currency
        DB::table('items')->where('currency', NULL)->update([
            'currency' => '$'
        ]);

        dd("Done");
    }
}
