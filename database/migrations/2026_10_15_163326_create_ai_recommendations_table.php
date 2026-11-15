
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_recommendations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('matching_result_id')
                ->constrained('matching_results')
                ->restrictOnDelete();

            $table->string('recommendation_type', 50);
            $table->text('content');

            $table->string('model_name', 100)->nullable();
            $table->string('status', 30)->default('pending');
            $table->text('error_message')->nullable();

            $table->timestamp('generated_at')->nullable();

            $table->timestamps();

            $table->index(['matching_result_id', 'created_at']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_recommendations');
    }
};
