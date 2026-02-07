<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('webhook_endpoints')) {
            return;
        }

        Schema::create('webhook_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('webhook_endpoint_id')->constrained()->cascadeOnDelete();
            $table->uuid('uuid')->unique();
            $table->string('event_type');
            $table->json('payload');
            $table->string('status', 20)->default('pending'); // pending, success, failed
            $table->unsignedSmallInteger('response_code')->nullable();
            $table->text('response_body')->nullable();
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();

            $table->index(['webhook_endpoint_id', 'created_at']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('webhook_deliveries');
    }
};
