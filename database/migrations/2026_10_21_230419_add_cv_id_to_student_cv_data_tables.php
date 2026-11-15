
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = [
            'student_educations',
            'student_experiences',
            'student_certifications',
            'student_projects',
        ];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->foreignId('cv_id')
                    ->nullable()
                    ->after('student_profile_id')
                    ->constrained('cvs')
                    ->restrictOnDelete();

                $table->index(['student_profile_id', 'cv_id']);
            });
        }
    }

    public function down(): void
    {
        $tables = [
            'student_educations',
            'student_experiences',
            'student_certifications',
            'student_projects',
        ];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropForeign(['cv_id']);
                $table->dropIndex(['student_profile_id', 'cv_id']);
                $table->dropColumn('cv_id');
            });
        }
    }
};
