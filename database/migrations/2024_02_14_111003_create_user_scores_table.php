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
        Schema::create('user_scores', function (Blueprint $table) {
            $table->id();
            $table->integer('score');
            $table->enum('type', ['quiz', 'product', 'receipt'])->default('quiz');
            $table->boolean('status')->default(false);
            $table->unsignedBigInteger('scorable_id');
            $table->string('scorable_type');
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->on('users')->references('id')->cascadeOnDelete();
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
        Schema::dropIfExists('user_scores');
    }
};
