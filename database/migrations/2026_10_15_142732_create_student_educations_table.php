
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('student_educations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('student_profile_id')
                ->constrained('student_profiles')
                ->restrictOnDelete();

            $table->string('institution', 200);
            $table->string('degree', 100)->nullable();
            $table->string('study_program', 150)->nullable();
            $table->string('field_of_study', 150)->nullable();

            $table->unsignedSmallInteger('start_year')->nullable();
            $table->unsignedSmallInteger('end_year')->nullable();

            $table->string('description', 500)->nullable();

            $table->timestamps();

            $table->index(['student_profile_id', 'start_year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_educations');
    }
};
