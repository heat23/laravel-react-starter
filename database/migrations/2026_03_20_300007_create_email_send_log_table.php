<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('email_send_logs')) {
            return;
        }
        Schema::create('email_send_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('sequence_type', 60);
            $table->unsignedTinyInteger('email_number');
            $table->timestamp('sent_at')->useCurrent();
            $table->unique(['user_id', 'sequence_type', 'email_number'], 'email_send_log_unique');
            $table->index(['sequence_type', 'email_number', 'sent_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_send_logs');
    }
};
