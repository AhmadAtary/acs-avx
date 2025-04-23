<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNetworkAnalysisResultsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('network_analysis_results', function (Blueprint $table) {
            $table->id(); // Auto-incrementing primary key
            $table->integer('cell_id')->index(); // Cell ID, indexed for quicker lookups
            $table->integer('device_count'); // Number of devices
            $table->decimal('avg_rsrp', 8, 2)->nullable(); // Average RSRP (nullable)
            $table->decimal('avg_rssi', 8, 2)->nullable(); // Average RSSI (nullable)
            $table->timestamps(); // Created at and updated at timestamps
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('network_analysis_results');
    }
}
