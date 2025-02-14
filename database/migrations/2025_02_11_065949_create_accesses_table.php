<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccessesTable extends Migration
{
    public function up()
    {
        Schema::create('accesses', function (Blueprint $table) {
            $table->id();
            $table->string('account_number')->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('role', ['owner', 'eng', 'cs'])->default('cs');
            $table->json('permissions');
            $table->integer('account_limit')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('accesses');
    }
}
