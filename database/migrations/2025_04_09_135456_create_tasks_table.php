<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();  // auto-incrementing primary key (default)
            $table->string('username');
            $table->string('device_id');
            $table->string('subject');
            $table->text('description');
            $table->string('user_email');
            $table->string('email');
            $table->timestamps();  // Automatically adds created_at and updated_at columns
        });
    }

    public function down()
    {
        Schema::dropIfExists('tasks');
    }
};
