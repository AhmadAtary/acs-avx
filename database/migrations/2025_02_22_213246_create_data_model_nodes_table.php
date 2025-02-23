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
        Schema::create('data_model_nodes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_model_id')->constrained()->onDelete('cascade'); // Links to Device Model
            $table->string('name');
            $table->string('path');
            $table->string('type')->nullable();
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_model_nodes');
    }
};
