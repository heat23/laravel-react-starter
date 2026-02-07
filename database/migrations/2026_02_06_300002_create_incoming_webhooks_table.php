<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! config('features.webhooks.enabled', false)) {
            return;
        }

        Schema::create('incoming_webhooks', function (Blueprint $table) {
            $table->id();
            $table->string('provider', 32);
            $table->string('external_id')->nullable();
            $table->string('event_type')->nullable();
            $table->json('payload');
            $table->string('status', 20)->default('received'); // received, processing, processed, failed
            $table->timestamps();

            $table->unique(['provider', 'external_id']);
            $table->index(['provider', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incoming_webhooks');
    }
};
