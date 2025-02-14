<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUploadProgressAndJobStatusesTables extends Migration
{
    public function up()
    {
        // Create the upload_progress table
        Schema::create('upload_progress', function (Blueprint $table) {
            $table->id(); // Creates a BIGINT UNSIGNED primary key
            $table->integer('total')->default(0);
            $table->integer('processed')->default(0);
            $table->integer('success_count')->default(0);
            $table->integer('fail_count')->default(0);
            $table->integer('not_found_count')->default(0);
            $table->string('status')->default('pending');
            $table->timestamp('paused_at')->nullable();
            $table->timestamp('resumed_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->string('action')->nullable();
            $table->string('nodePath')->nullable();
            $table->string('newValue')->nullable();
            $table->string('nodeTypeDetailed')->nullable();
            $table->string('deviceModel')->nullable();
            $table->timestamps();
        });

        // Create the job_statuses table without a foreign key
        Schema::create('job_statuses', function (Blueprint $table) {
            $table->id(); // Creates a BIGINT UNSIGNED primary key
            $table->unsignedBigInteger('upload_progress_id'); // Add column for foreign key
            $table->string('serial_number');
            $table->string('status')->default('pending');
            $table->text('response')->nullable();
            $table->timestamps();
        });

        // Add the foreign key relationship
        Schema::table('job_statuses', function (Blueprint $table) {
            $table->foreign('upload_progress_id')
                ->references('id')
                ->on('upload_progress')
                ->onDelete('cascade'); // Add the foreign key constraint
        });
    }

    public function down()
    {
        // Drop the foreign key first
        Schema::table('job_statuses', function (Blueprint $table) {
            $table->dropForeign(['upload_progress_id']); // Drop the foreign key
        });

        // Drop the tables
        Schema::dropIfExists('job_statuses');
        Schema::dropIfExists('upload_progress');
    }
}
