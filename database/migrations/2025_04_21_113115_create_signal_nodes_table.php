<?php

// database/migrations/xxxx_xx_xx_create_signal_nodes_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('signal_nodes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('model_id');
            $table->string('param_name'); // Example: RSRP, SINR
            $table->string('node_path');  // Path in JSON data from device
            $table->timestamps();

            $table->foreign('model_id')->references('id')->on('device_models')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('signal_nodes');
    }
};
