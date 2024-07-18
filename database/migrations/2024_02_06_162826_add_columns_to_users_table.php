<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
//            $table->renameColumn('first_name', 'name');
            $table->removeColumn('last_name');
            $table->removeColumn('speciality_id');
            $table->removeColumn('SMS_TOKEN');
            $table->unsignedBigInteger('pharmacy_id')->after('city_id')->nullable();
            $table->foreign('pharmacy_id')->references('id')->on('pharmacies')->cascadeOnDelete();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};
