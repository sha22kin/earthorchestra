<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('arrangements', function (Blueprint $table) {
            $table->id();
            $table->char('code', 8)->unique();
            $table->string('title', 60)->nullable();
            $table->json('levels');
            $table->unsignedInteger('listens')->default(0);
            $table->char('creator_hash', 64)->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('arrangements');
    }
};
