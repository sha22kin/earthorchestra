<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visualisations', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 64)->unique();
            $table->unsignedTinyInteger('position')->index();
            $table->string('title');
            $table->string('short_title', 40);
            $table->string('model', 16);
            $table->string('measurement');                    // e.g. "3 m above the surface"
            $table->enum('interaction', ['cursor', 'slider'])->default('cursor');
            $table->string('accent', 7);
            $table->text('summary');
            $table->json('body');
            $table->json('legend');
            $table->string('video_path');
            $table->string('poster_path');
            $table->string('audio_path');
            $table->unsignedSmallInteger('svs_id');
            $table->date('data_starts_on');
            $table->date('data_ends_on');
            $table->timestamps();
        });

        Schema::create('citations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visualisation_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('position');
            $table->text('reference');
            $table->string('url', 500)->nullable();
            $table->timestamps();

            $table->unique(['visualisation_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('citations');
        Schema::dropIfExists('visualisations');
    }
};
