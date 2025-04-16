<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('device_standard_nodes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_model_id')->constrained('device_models')->onDelete('cascade');
            $table->string('node_path'); // Node path (e.g., "InternetGatewayDevice.SystemConfig.WiFi.NeighborAP.1.BSSID")
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('device_standard_nodes');
    }
};
