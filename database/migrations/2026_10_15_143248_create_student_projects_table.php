
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_projects', function (Blueprint $table) {
            $table->id();

            $table->foreignId('student_profile_id')
                ->constrained('student_profiles')
                ->restrictOnDelete();

            $table->string('name', 200);
            $table->text('description')->nullable();
            $table->string('role', 150)->nullable();
            $table->string('project_url', 500)->nullable();

            $table->date('started_at')->nullable();
            $table->date('completed_at')->nullable();

            $table->timestamps();

            $table->index(['student_profile_id', 'started_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_projects');
    }
};
