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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('phone');
            $table->string('language')->default('en');
            $table->unsignedBigInteger('city_id')->after('language')->nullable();
            $table->foreign('city_id')->references('id')->on('cities')->cascadeOnDelete();
            $table->unsignedBigInteger('speciality_id')->nullable();
            $table->unsignedBigInteger('type_id');
            $table->foreign('type_id')->on('types')->references('id')->cascadeOnDelete();
            $table->foreign('speciality_id')->on('specialities')->references('id')->cascadeOnDelete();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
};
