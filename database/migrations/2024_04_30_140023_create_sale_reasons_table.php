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
        Schema::create('sale_reasons', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('reason_id');
            $table->foreign('reason_id')->on('rejection_reasons')->references('id')->cascadeOnDelete();
            $table->unsignedBigInteger('sale_id');
            $table->foreign('sale_id')->on('sales')->references('id')->cascadeOnDelete();
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
        Schema::dropIfExists('sale_reasons');
    }
};
