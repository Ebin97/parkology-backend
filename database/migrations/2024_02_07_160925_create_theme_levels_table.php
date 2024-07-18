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
        Schema::create('theme_levels', function (Blueprint $table) {
            $table->id();
            $table->string('level')->nullable();
            $table->integer('orders')->default(1000);
            $table->boolean('active')->default(false);
            $table->unsignedBigInteger('theme_id');
            $table->foreign('theme_id')->on('themes')->references('id')->cascadeOnDelete();
            $table->unsignedBigInteger('quiz_id');
            $table->foreign('quiz_id')->on('quizzes')->references('id')->cascadeOnDelete();
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
        Schema::dropIfExists('theme_levels');
    }
};
