<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feature_flag_overrides', function (Blueprint $table) {
            $table->id();
            $table->string('flag', 64);
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->boolean('enabled');
            $table->string('reason', 255)->nullable();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            // Global overrides: one row per flag (user_id = NULL)
            // User overrides: one row per flag+user combination
            $table->unique(['flag', 'user_id']);

            // Index for fast user-specific lookups
            $table->index(['user_id', 'flag']);

            // Index for global override lookups (WHERE user_id IS NULL AND flag = ?)
            $table->index('flag');

            // Index for audit trail queries ("who changed what")
            $table->index('changed_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feature_flag_overrides');
    }
};
