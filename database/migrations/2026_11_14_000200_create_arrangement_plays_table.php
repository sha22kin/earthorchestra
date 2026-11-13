<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * One row each time a shared arrangement link is opened. Lets the admin
 * dashboard chart listens per day (the arrangements.listens column is only a total).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('arrangement_plays', function (Blueprint $table) {
            $table->id();
            $table->foreignId('arrangement_id')->constrained()->cascadeOnDelete();
            $table->timestamp('created_at')->useCurrent()->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('arrangement_plays');
    }
};
