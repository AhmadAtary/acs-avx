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
        Schema::create('hosts', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->string('Model'); // Model name
            $table->string('count')->nullable(); // Product class
            $table->string('HostName')->nullable(); // Host name
            $table->string('IPAddress')->nullable(); // IP address
            $table->string('MACAddress')->nullable(); // MAC address
            $table->integer('RSSI')->nullable(); // Signal strength
            $table->string('hostPath')->nullable(); // Host path
            $table->timestamps(); // Created and updated timestamps
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hosts');
    }
};
