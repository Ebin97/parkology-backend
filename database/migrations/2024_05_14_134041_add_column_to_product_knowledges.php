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
        Schema::table('product_knowledge', function (Blueprint $table) {
            $table->unsignedBigInteger('type_id')->after('active')->nullable();
            $table->foreign('type_id')->on('types')->references('id')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('product_knowledge', function (Blueprint $table) {
            //
        });
    }
};
