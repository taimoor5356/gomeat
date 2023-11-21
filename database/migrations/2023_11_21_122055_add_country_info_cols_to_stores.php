<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCountryInfoColsToStores extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('stores', function (Blueprint $table) {
            //
            $table->string('country_id')->nullable();
            $table->string('state_id')->nullable();
            $table->string('store_online_payment')->nullable();
            $table->string('store_cash_payment')->nullable();
            $table->string('filer_status')->nullable();
            $table->string('restaurant_online_payment')->nullable();
            $table->string('restaurant_cash_payment')->nullable();
            $table->json('country_info')->nullable();
            $table->json('state_info')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('stores', function (Blueprint $table) {
            //
        });
    }
}
