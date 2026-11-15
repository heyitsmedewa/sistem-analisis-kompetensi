<?php

namespace Database\Seeders;

use App\Models\Skill;
use App\Models\SkillSynonym;
use Illuminate\Database\Seeder;

class SkillSynonymSeeder extends Seeder
{
    public function run(): void
    {
        $synonyms = [
            'JavaScript' => ['JS', 'ECMAScript'],
            'PHP' => ['PHP Programming'],
            'HTML' => ['HTML5'],
            'CSS' => ['CSS3'],
            'MySQL' => ['My SQL'],
            'Microsoft Excel' => ['MS Excel', 'Excel'],
            'Microsoft Word' => ['MS Word', 'Microsoft Office Word'],
            'Graphic Design' => ['Desain Grafis', 'Graphic Designer'],
            'Video Editing' => ['Editing Video', 'Penyuntingan Video'],
            'Communication' => ['Komunikasi'],
            'Teamwork' => ['Kerja Tim', 'Team Work'],
            'Problem Solving' => ['Pemecahan Masalah'],
        ];

        foreach ($synonyms as $skillName => $terms) {
            $skill = Skill::where('name', $skillName)->first();

            if (! $skill) {
                continue;
            }

            foreach (array_unique($terms) as $term) {
                SkillSynonym::updateOrCreate(
                    [
                        'skill_id' => $skill->id,
                        'synonym' => $term,
                    ],
                    [
                        'approval_status' => 'approved',
                    ]
                );
            }
        }
    }
}
