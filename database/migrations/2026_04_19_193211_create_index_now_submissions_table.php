<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! config('features.indexnow.enabled', false)) {
            return;
        }

        Schema::create('indexnow_submissions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->json('urls');
            $table->unsignedSmallInteger('url_count');
            $table->string('status', 20)->default('pending'); // pending, success, failed
            $table->unsignedSmallInteger('response_code')->nullable();
            $table->text('response_body')->nullable();
            $table->unsignedSmallInteger('attempts')->default(0);
            $table->timestamp('submitted_at')->nullable();
            $table->string('trigger', 50)->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index('trigger');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('indexnow_submissions');
    }
};
