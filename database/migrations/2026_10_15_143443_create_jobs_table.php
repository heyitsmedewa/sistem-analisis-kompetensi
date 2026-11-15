
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_postings', function (Blueprint $table) {
            $table->id();

            $table->string('title', 200);
            $table->string('company', 200)->nullable();
            $table->text('description')->nullable();

            $table->string('education_requirement', 200)->nullable();
            $table->text('experience_requirement')->nullable();
            $table->string('location', 200)->nullable();

            $table->string('source_name', 100)->nullable();
            $table->string('source_url', 500)->nullable();
            $table->timestamp('collected_at')->nullable();

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index(['is_active', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_postings');
    }
};
