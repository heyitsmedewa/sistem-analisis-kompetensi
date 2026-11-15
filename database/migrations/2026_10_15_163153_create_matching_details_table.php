
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('matching_details', function (Blueprint $table) {
            $table->id();

            $table->foreignId('matching_result_id')
                ->constrained('matching_results')
                ->restrictOnDelete();

            $table->foreignId('skill_id')
                ->constrained('skills')
                ->restrictOnDelete();

            $table->unsignedTinyInteger('actual_level')->nullable();
            $table->unsignedTinyInteger('required_level')->nullable();

            $table->smallInteger('gap')->nullable();
            $table->decimal('gap_weight', 6, 3)->nullable();

            $table->string('factor_type', 30)->nullable();
            $table->string('evaluation_status', 30)->default('pending');

            $table->timestamps();

            $table->unique(['matching_result_id', 'skill_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('matching_details');
    }
};
