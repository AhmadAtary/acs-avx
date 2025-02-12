<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class DropUploadProgressAndJobStatusesTables extends Migration
{
    public function up()
    {
        Schema::dropIfExists('job_statuses');
        Schema::dropIfExists('upload_progress');
    }

    public function down()
    {
        // Optionally, recreate the tables if you want to allow rollback
    }
}
