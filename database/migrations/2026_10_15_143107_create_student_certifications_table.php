
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_certifications', function (Blueprint $table) {
            $table->id();

            $table->foreignId('student_profile_id')
                ->constrained('student_profiles')
                ->restrictOnDelete();

            $table->string('name', 200);
            $table->string('issuer', 200)->nullable();

            $table->date('issued_at')->nullable();
            $table->date('expires_at')->nullable();

            $table->string('credential_url', 500)->nullable();

            $table->timestamps();

            $table->index(['student_profile_id', 'issued_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_certifications');
    }
};
