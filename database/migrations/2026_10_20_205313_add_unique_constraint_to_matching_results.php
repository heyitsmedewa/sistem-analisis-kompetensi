
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('matching_results', function (Blueprint $table) {
            $table->unique(
                ['student_profile_id', 'job_posting_id'],
                'matching_results_student_job_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('matching_results', function (Blueprint $table) {
            $table->dropUnique('matching_results_student_job_unique');
        });
    }
};
