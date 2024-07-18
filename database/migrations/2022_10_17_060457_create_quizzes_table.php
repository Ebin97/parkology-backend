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
        Schema::create('quizzes', function (Blueprint $table) {
            $table->id();
            $table->text('slug');
            $table->text('title');
            $table->integer('level')->default(-1);
            $table->boolean('active')->default(false);//is the quiz available in the game?
            //This columns only for the Bonus Quiz and it`s connected with the quiz_type table
            $table->boolean('bonus')->default(false);//mark it as bonus quiz
            $table->dateTime('start_time')->nullable();
            $table->dateTime('end_time')->nullable();
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
        Schema::dropIfExists('quizzes');
    }
};
