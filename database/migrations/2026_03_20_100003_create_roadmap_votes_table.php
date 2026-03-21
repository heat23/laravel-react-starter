<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roadmap_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('entry_slug');
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['user_id', 'entry_slug']);
            $table->index('entry_slug');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('roadmap_votes');
    }
};
