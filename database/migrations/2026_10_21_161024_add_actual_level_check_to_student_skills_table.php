<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE student_skills
            ADD CONSTRAINT chk_student_skills_actual_level
            CHECK (
                actual_level IS NULL
                OR actual_level BETWEEN 1 AND 5
            )
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE student_skills
            DROP CONSTRAINT chk_student_skills_actual_level
        ");
    }
};
