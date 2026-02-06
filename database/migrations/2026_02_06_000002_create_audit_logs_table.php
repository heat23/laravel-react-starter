<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('audit_logs')) {
            Schema::create('audit_logs', function (Blueprint $table) {
                $table->id();
                $table->string('event', 64);
                // nullOnDelete: soft-deleted users retain user_id (FK not triggered);
                // force-deleted users get user_id set to null. Intentional —
                // audit logs are preserved for compliance regardless of user lifecycle.
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->string('ip', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamp('created_at')->nullable();

                $table->index(['event', 'created_at']);
                $table->index(['user_id', 'created_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
