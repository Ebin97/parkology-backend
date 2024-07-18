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
        Schema::table('notifiactions', function (Blueprint $table) {
            $table->string('notifiable_type')->after('id');
            $table->unsignedBigInteger('notifiable_id')->after('id');
            $table->boolean('status')->default(false)->after('title');
            $table->unsignedBigInteger('user_id')->nullable()->after('title');
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->enum('type', ['private', 'public', 'role', 'challenge'])->after('title');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('notifiactions', function (Blueprint $table) {
            //
        });
    }
};
