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

        Schema::create('webhook_endpoints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('url');
            $table->json('events');
            $table->text('secret');
            $table->string('description')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('webhook_endpoints');
    }
};
