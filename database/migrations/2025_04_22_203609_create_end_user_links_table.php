<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEndUserLinksTable extends Migration
{
    public function up()
    {
        Schema::create('end_user_links', function (Blueprint $table) {
            $table->id();
            $table->string('token')->unique();
            $table->string('username');
            $table->string('password');
            $table->timestamp('expires_at');
            $table->boolean('is_used')->default(false);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('end_user_links');
    }
}
