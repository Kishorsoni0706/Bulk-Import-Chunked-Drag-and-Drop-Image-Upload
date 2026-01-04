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
        Schema::create('images', function (Blueprint $table) {
             $table->id();
             $table->unsignedBigInteger('upload_id');
             $table->string('filename');
             $table->string('disk')->default('public');
             $table->string('path');  // path to original merged image
             $table->json('variants');  // JSON, e.g. { "256": "path256.jpg", "512": ..., "1024": ... }
             $table->timestamps();

             $table->foreign('upload_id')->references('id')->on('uploads')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('images');
    }
};
