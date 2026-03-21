<?php

use App\Models\RoadmapEntry;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roadmap_entries', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('status')->default('planned'); // planned, in_progress, completed
            $table->unsignedInteger('display_order')->default(0);
            $table->timestamps();

            $table->index(['status', 'display_order']);
        });

        // Add roadmap_entry_id to feedback_submissions for linking
        Schema::table('feedback_submissions', function (Blueprint $table) {
            $table->foreignId('roadmap_entry_id')
                ->nullable()
                ->after('admin_notes')
                ->constrained('roadmap_entries')
                ->nullOnDelete();
            $table->index('roadmap_entry_id');
        });
    }

    public function down(): void
    {
        Schema::table('feedback_submissions', function (Blueprint $table) {
            $table->dropForeignIdFor(RoadmapEntry::class);
            $table->dropColumn('roadmap_entry_id');
        });

        Schema::dropIfExists('roadmap_entries');
    }
};
