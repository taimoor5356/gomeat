<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCountryHasStatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('country_has_states', function (Blueprint $table) {
            $table->id();
            $table->string('country_id');
            $table->string('name');
            $table->string('store_online_payment');
            $table->string('store_cash_payment');
            $table->string('restaurant_online_payment');
            $table->string('restaurant_cash_payment');
            $table->string('deleted_at')->nullable();
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
        Schema::dropIfExists('country_has_states');
    }
}
