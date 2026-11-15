<?php

namespace Database\Seeders;

use App\Models\Skill;
use Illuminate\Database\Seeder;

class SkillSeeder extends Seeder
{
    public function run(): void
    {
        $skills = [
            ['name' => 'PHP', 'category' => 'Programming'],
            ['name' => 'JavaScript', 'category' => 'Programming'],
            ['name' => 'Python', 'category' => 'Programming'],
            ['name' => 'Laravel', 'category' => 'Web Development'],
            ['name' => 'HTML', 'category' => 'Web Development'],
            ['name' => 'CSS', 'category' => 'Web Development'],
            ['name' => 'SQL', 'category' => 'Database'],
            ['name' => 'MySQL', 'category' => 'Database'],
            ['name' => 'Microsoft Excel', 'category' => 'Office'],
            ['name' => 'Microsoft Word', 'category' => 'Office'],
            ['name' => 'Graphic Design', 'category' => 'Design'],
            ['name' => 'Video Editing', 'category' => 'Multimedia'],
            ['name' => 'Communication', 'category' => 'Soft Skill'],
            ['name' => 'Teamwork', 'category' => 'Soft Skill'],
            ['name' => 'Problem Solving', 'category' => 'Soft Skill'],
        ];

        foreach ($skills as $skill) {
            Skill::updateOrCreate(
                ['name' => $skill['name']],
                $skill
            );
        }
    }
}