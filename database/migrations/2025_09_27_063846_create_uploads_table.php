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
        Schema::create('uploads', function (Blueprint $table) {
           $table->id();
           $table->string('upload_uuid')->unique();
           $table->string('filename');
           $table->string('disk')->default('public');
           $table->string('path')->nullable();
           $table->unsignedBigInteger('size')->nullable();
           $table->string('checksum')->nullable();
           $table->enum('status', ['uploading','merged','failed'])->default('uploading');
           $table->timestamps();
           });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('uploads');
    }
};
