
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('matching_results', function (Blueprint $table) {
            $table->id();

            $table->foreignId('student_profile_id')
                ->constrained('student_profiles')
                ->restrictOnDelete();

            $table->foreignId('job_posting_id')
                ->constrained('job_postings')
                ->restrictOnDelete();

            $table->decimal('final_score', 8, 4)->nullable();
            $table->string('status', 30)->default('pending');
            $table->timestamp('calculated_at')->nullable();

            $table->timestamps();

            $table->index(['student_profile_id', 'created_at']);
            $table->index(['job_posting_id', 'final_score']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('matching_results');
    }
};
