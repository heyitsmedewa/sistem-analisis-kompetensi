<?php

namespace Tests\Unit;

use App\Services\GeminiCvExtractor;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class GeminiCvExtractorTest extends TestCase
{
    public function test_empty_cv_text_is_rejected(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Teks CV tidak boleh kosong.'
        );

        app(GeminiCvExtractor::class)->extract('   ');
    }

    public function test_missing_groq_configuration_is_rejected(): void
    {
        config([
            'services.groq.key' => null,
            'services.groq.model' => null,
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Konfigurasi Groq belum lengkap.'
        );

        app(GeminiCvExtractor::class)->extract(
            'Nama: Budi Santoso'
        );
    }

    public function test_valid_json_response_is_processed(): void
    {
        config([
            'services.groq.key' => 'test-api-key',
            'services.groq.model' => 'test-model',
        ]);

        $expected = [
            'personal_info' => [
                'full_name' => 'Budi Santoso',
                'email' => null,
                'phone' => null,
                'address' => null,
                'linkedin_url' => null,
                'portfolio_url' => null,
            ],
            'profile_summary' => null,
            'educations' => [],
            'experiences' => [],
            'hard_skills' => ['PHP', 'Laravel'],
            'soft_skills' => [],
            'languages' => [],
            'organizations' => [],
            'certifications' => [],
            'projects' => [],
            'achievements' => [],
            'missing_or_uncertain' => [],
        ];

        Http::fake([
            'api.groq.com/*' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => json_encode($expected),
                        ],
                    ],
                ],
            ], 200),
        ]);

        $result = app(GeminiCvExtractor::class)->extract(
            'Nama: Budi Santoso. Keahlian: PHP dan Laravel.'
        );

        $this->assertSame($expected, $result);

        Http::assertSent(function ($request) {
            return $request->url()
                === 'https://api.groq.com/openai/v1/chat/completions'
                && $request->hasHeader(
                    'Authorization',
                    'Bearer test-api-key'
                );
        });
    }

    public function test_invalid_json_response_is_rejected(): void
    {
        config([
            'services.groq.key' => 'test-api-key',
            'services.groq.model' => 'test-model',
        ]);

        Http::fake([
            'api.groq.com/*' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => 'Ini bukan JSON',
                        ],
                    ],
                ],
            ], 200),
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Respons Groq bukan JSON valid.'
        );

        app(GeminiCvExtractor::class)->extract(
            'Nama: Budi Santoso'
        );
    }
    
public function test_groq_http_failure_is_rejected(): void
{
    config([
        'services.groq.key' => 'test-api-key',
        'services.groq.model' => 'test-model',
    ]);

    Http::fake([
        'api.groq.com/*' => Http::response([
            'error' => [
                'message' => 'Rate limit exceeded',
            ],
        ], 429),
    ]);

    $this->expectException(RuntimeException::class);
    $this->expectExceptionMessage(
        'Permintaan Groq gagal dengan HTTP 429.'
    );

    app(GeminiCvExtractor::class)->extract(
        'Nama: Budi Santoso'
    );
}

public function test_incomplete_json_structure_is_rejected(): void
{
    config([
        'services.groq.key' => 'test-api-key',
        'services.groq.model' => 'test-model',
    ]);

    Http::fake([
        'api.groq.com/*' => Http::response([
            'choices' => [
                [
                    'message' => [
                        'content' => json_encode([
                            'personal_info' => [
    'full_name' => 'Budi Santoso',
],
                            'hard_skills' => [],
                        ]),
                    ],
                ],
            ],
        ], 200),
    ]);

    $this->expectException(RuntimeException::class);
    $this->expectExceptionMessage(
        'Struktur JSON Groq tidak lengkap: profile_summary'
    );

    app(GeminiCvExtractor::class)->extract(
        'Nama: Budi Santoso'
    );
}

public function test_hard_skills_object_is_rejected(): void
{
    config([
        'services.groq.key' => 'test-api-key',
        'services.groq.model' => 'test-model',
    ]);

    $invalidData = [
        'personal_info' => [
    'full_name' => 'Budi Santoso',
],
        'profile_summary' => null,
        'educations' => [],
        'experiences' => [],
        'hard_skills' => ['PHP' => 'advanced'],
        'soft_skills' => [],
        'languages' => [],
        'organizations' => [],
        'certifications' => [],
        'projects' => [],
        'achievements' => [],
        'missing_or_uncertain' => [],
    ];

    Http::fake([
        'api.groq.com/*' => Http::response([
            'choices' => [[
                'message' => [
                    'content' => json_encode($invalidData),
                ],
            ]],
        ], 200),
    ]);

    $this->expectException(RuntimeException::class);
    $this->expectExceptionMessage(
        'Struktur JSON Groq tidak valid: hard_skills harus berupa array string.'
    );

    app(GeminiCvExtractor::class)->extract(
        'Nama: Budi Santoso'
    );
}

public function test_educations_string_is_rejected(): void
{
    config([
        'services.groq.key' => 'test-api-key',
        'services.groq.model' => 'test-model',
    ]);

    $invalidData = [
        'personal_info' => [
    'full_name' => 'Budi Santoso',
],
        'profile_summary' => null,
        'educations' => 'Universitas Contoh',
        'experiences' => [],
        'hard_skills' => [],
        'soft_skills' => [],
        'languages' => [],
        'organizations' => [],
        'certifications' => [],
        'projects' => [],
        'achievements' => [],
        'missing_or_uncertain' => [],
    ];

    Http::fake([
        'api.groq.com/*' => Http::response([
            'choices' => [[
                'message' => [
                    'content' => json_encode($invalidData),
                ],
            ]],
        ], 200),
    ]);

    $this->expectException(RuntimeException::class);
    $this->expectExceptionMessage(
        'Struktur JSON Groq tidak valid: educations harus berupa array objek.'
    );

    app(GeminiCvExtractor::class)->extract(
        'Pendidikan: Universitas Contoh'
    );
}

public function test_personal_info_string_is_rejected(): void
{
    config([
        'services.groq.key' => 'test-api-key',
        'services.groq.model' => 'test-model',
    ]);

    $invalidData = [
        'personal_info' => 'Budi Santoso',
        'profile_summary' => null,
        'educations' => [],
        'experiences' => [],
        'hard_skills' => [],
        'soft_skills' => [],
        'languages' => [],
        'organizations' => [],
        'certifications' => [],
        'projects' => [],
        'achievements' => [],
        'missing_or_uncertain' => [],
    ];

    Http::fake([
        'api.groq.com/*' => Http::response([
            'choices' => [[
                'message' => [
                    'content' => json_encode($invalidData),
                ],
            ]],
        ], 200),
    ]);

    $this->expectException(RuntimeException::class);
    $this->expectExceptionMessage(
        'Struktur JSON Groq tidak valid: personal_info harus berupa objek.'
    );

    app(GeminiCvExtractor::class)->extract(
        'Nama: Budi Santoso'
    );
}

public function test_profile_summary_array_is_rejected(): void
{
    config([
        'services.groq.key' => 'test-api-key',
        'services.groq.model' => 'test-model',
    ]);

    $invalidData = [
        'personal_info' => [
            'full_name' => 'Budi Santoso',
        ],
        'profile_summary' => ['Ringkasan CV'],
        'educations' => [],
        'experiences' => [],
        'hard_skills' => [],
        'soft_skills' => [],
        'languages' => [],
        'organizations' => [],
        'certifications' => [],
        'projects' => [],
        'achievements' => [],
        'missing_or_uncertain' => [],
    ];

    Http::fake([
        'api.groq.com/*' => Http::response([
            'choices' => [[
                'message' => [
                    'content' => json_encode($invalidData),
                ],
            ]],
        ], 200),
    ]);

    $this->expectException(RuntimeException::class);
    $this->expectExceptionMessage(
        'Struktur JSON Groq tidak valid: profile_summary harus berupa string atau null.'
    );

    app(GeminiCvExtractor::class)->extract(
        'Nama: Budi Santoso'
    );
}

public function test_education_start_year_invalid_format_is_rejected(): void
{
    config([
        'services.groq.key' => 'test-api-key',
        'services.groq.model' => 'test-model',
    ]);

    $invalidData = [
        'personal_info' => [
            'full_name' => 'Budi Santoso',
        ],
        'profile_summary' => null,
        'educations' => [
            [
                'institution' => 'Universitas Contoh',
                'degree' => 'S1',
                'study_program' => 'Informatika',
                'field_of_study' => 'Teknologi Informasi',
                'start_year' => 'kemarin',
                'end_year' => null,
                'description' => null,
            ],
        ],
        'experiences' => [],
        'hard_skills' => [],
        'soft_skills' => [],
        'languages' => [],
        'organizations' => [],
        'certifications' => [],
        'projects' => [],
        'achievements' => [],
        'missing_or_uncertain' => [],
    ];

    Http::fake([
        'api.groq.com/*' => Http::response([
            'choices' => [[
                'message' => [
                    'content' => json_encode($invalidData),
                ],
            ]],
        ], 200),
    ]);

    $this->expectException(RuntimeException::class);
    $this->expectExceptionMessage(
        'Struktur JSON Groq tidak valid: educations.start_year harus berupa tahun empat digit atau null.'
    );

    app(GeminiCvExtractor::class)->extract(
        'Pendidikan: Universitas Contoh'
    );
}

public function test_education_end_year_invalid_format_is_rejected(): void
{
    config([
        'services.groq.key' => 'test-api-key',
        'services.groq.model' => 'test-model',
    ]);

    $invalidData = [
        'personal_info' => [
            'full_name' => 'Budi Santoso',
        ],
        'profile_summary' => null,
        'educations' => [
            [
                'institution' => 'Universitas Contoh',
                'degree' => 'S1',
                'study_program' => 'Informatika',
                'field_of_study' => 'Teknologi Informasi',
                'start_year' => '2020',
                'end_year' => 'sekarang',
                'description' => null,
            ],
        ],
        'experiences' => [],
        'hard_skills' => [],
        'soft_skills' => [],
        'languages' => [],
        'organizations' => [],
        'certifications' => [],
        'projects' => [],
        'achievements' => [],
        'missing_or_uncertain' => [],
    ];

    Http::fake([
        'api.groq.com/*' => Http::response([
            'choices' => [[
                'message' => [
                    'content' => json_encode($invalidData),
                ],
            ]],
        ], 200),
    ]);

    $this->expectException(RuntimeException::class);
    $this->expectExceptionMessage(
        'Struktur JSON Groq tidak valid: educations.end_year harus berupa tahun empat digit atau null.'
    );

    app(GeminiCvExtractor::class)->extract(
        'Pendidikan: Universitas Contoh, 2020-sekarang'
    );
}

public function test_experience_is_current_string_is_rejected(): void
{
    config([
        'services.groq.key' => 'test-api-key',
        'services.groq.model' => 'test-model',
    ]);

    $invalidData = [
        'personal_info' => [
            'full_name' => 'Budi Santoso',
        ],
        'profile_summary' => null,
        'educations' => [],
        'experiences' => [
            [
                'organization' => 'PT Contoh',
                'position' => 'Programmer',
                'employment_type' => 'Full-time',
                'start_date' => '2024-01',
                'end_date' => null,
                'is_current' => 'true',
                'description' => 'Mengembangkan aplikasi.',
            ],
        ],
        'hard_skills' => [],
        'soft_skills' => [],
        'languages' => [],
        'organizations' => [],
        'certifications' => [],
        'projects' => [],
        'achievements' => [],
        'missing_or_uncertain' => [],
    ];

    Http::fake([
        'api.groq.com/*' => Http::response([
            'choices' => [[
                'message' => [
                    'content' => json_encode($invalidData),
                ],
            ]],
        ], 200),
    ]);

    $this->expectException(RuntimeException::class);
    $this->expectExceptionMessage(
        'Struktur JSON Groq tidak valid: experiences.is_current harus berupa boolean.'
    );

    app(GeminiCvExtractor::class)->extract(
        'Pengalaman: Programmer di PT Contoh sejak Januari 2024.'
    );
}

public function test_experience_start_date_invalid_format_is_rejected(): void
{
    config([
        'services.groq.key' => 'test-key',
        'services.groq.model' => 'test-model',
    ]);

    $invalidData = [
        'personal_info' => [
            'full_name' => 'Budi',
            'email' => null,
            'phone' => null,
            'address' => null,
            'linkedin_url' => null,
            'portfolio_url' => null,
        ],
        'profile_summary' => null,
        'educations' => [],
        'experiences' => [
            [
                'organization' => 'PT Contoh',
                'position' => 'Programmer',
                'employment_type' => 'Full-time',
                'start_date' => 'Januari 2024',
                'end_date' => null,
                'is_current' => true,
                'description' => 'Mengembangkan aplikasi.',
            ],
        ],
        'hard_skills' => [],
        'soft_skills' => [],
        'languages' => [],
        'organizations' => [],
        'certifications' => [],
        'projects' => [],
        'achievements' => [],
        'missing_or_uncertain' => [],
    ];

    Http::fake([
        'api.groq.com/*' => Http::response([
            'choices' => [
                [
                    'message' => [
                        'content' => json_encode($invalidData),
                    ],
                ],
            ],
        ], 200),
    ]);

    $this->expectException(\RuntimeException::class);
    $this->expectExceptionMessage(
        'Struktur JSON Groq tidak valid: experiences.start_date harus berupa tanggal YYYY-MM-DD, YYYY-MM, YYYY, atau null.'
    );

    app(\App\Services\GeminiCvExtractor::class)->extract('Contoh CV');
}

}
