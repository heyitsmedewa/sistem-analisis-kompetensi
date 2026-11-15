
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
        Schema::create('skill_synonyms', function (Blueprint $table) {
            $table->id();

            $table->foreignId('skill_id')
                ->constrained('skills')
                ->restrictOnDelete();

            $table->string('synonym', 150);
            $table->timestamps();

            $table->unique(['skill_id', 'synonym']);
            $table->index('synonym');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('skill_synonyms');
    }
};
