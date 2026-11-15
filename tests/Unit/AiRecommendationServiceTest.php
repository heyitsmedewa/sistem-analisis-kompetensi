<?php

namespace Tests\Unit;

use App\Models\JobPosting;
use App\Models\MatchingDetail;
use App\Models\MatchingResult;
use App\Models\Skill;
use App\Models\StudentProfile;
use App\Models\User;
use App\Services\AiRecommendationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class AiRecommendationServiceTest extends TestCase
{
    use RefreshDatabase;

    private function createMatchingResult(): MatchingResult
    {
        $user = User::create([
            'name' => 'Test Student',
            'email' => 'ai-recommendation-test@example.com',
            'password' => 'password',
        ]);

        $profile = StudentProfile::create([
            'user_id' => $user->id,
            'student_number' => 'AI-TEST001',
            'study_program' => 'Informatika',
            'institution' => 'Kampus Pengujian',
            'education_level' => 'S1',
        ]);

        $job = JobPosting::create([
            'title' => 'Test Programmer',
            'company' => 'Test Company',
            'is_active' => true,
        ]);

        $skill = Skill::create([
            'name' => 'Test Programming',
            'category' => 'technical',
            'is_active' => true,
        ]);

        $result = MatchingResult::create([
            'student_profile_id' => $profile->id,
            'job_posting_id' => $job->id,
            'final_score' => 4.0000,
            'status' => 'final',
        ]);

        MatchingDetail::create([
            'matching_result_id' => $result->id,
            'skill_id' => $skill->id,
            'actual_level' => 2,
            'required_level' => 4,
            'gap' => -2,
            'gap_weight' => 3.000,
            'factor_type' => 'core',
            'evaluation_status' => 'evaluated',
        ]);

        return $result;
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

        app(AiRecommendationService::class)->generate(
            new MatchingResult()
        );
    }

    public function test_valid_json_response_is_processed(): void
    {
        config([
            'services.groq.key' => 'test-api-key',
            'services.groq.model' => 'test-model',
        ]);

        $expected = [
    'summary' => 'Fokus pada kompetensi yang masih memiliki GAP.',
    'verified_gaps' => [
        [
            'skill' => 'Test Programming',
            'actual_level' => 2,
            'required_level' => 4,
            'priority' => 'high',
            'reason' => 'Level aktual masih di bawah persyaratan.',
            'actions' => ['Latihan pemrograman secara rutin.'],
        ],
    ],
    'verification_needed' => [],
    'development_plan' => [],
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

        $result = app(AiRecommendationService::class)->generate(
            $this->createMatchingResult()
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

    public function test_missing_verified_gap_is_rejected(): void
{
    config([
        'services.groq.key' => 'test-api-key',
        'services.groq.model' => 'test-model',
    ]);

    $response = [
        'summary' => 'Fokus pada kompetensi.',
        'verified_gaps' => [],
        'verification_needed' => [],
        'development_plan' => [],
    ];

    Http::fake([
        'api.groq.com/*' => Http::response([
            'choices' => [
                [
                    'message' => [
                        'content' => json_encode($response),
                    ],
                ],
            ],
        ], 200),
    ]);

    $this->expectException(\RuntimeException::class);
    $this->expectExceptionMessage(
        'AI tidak menyertakan seluruh kesenjangan kompetensi terverifikasi.'
    );

    app(AiRecommendationService::class)->generate(
        $this->createMatchingResult()
    );
}


    public function test_duplicate_verified_gap_is_rejected(): void
{
    config([
        'services.groq.key' => 'test-api-key',
        'services.groq.model' => 'test-model',
    ]);

    $response = [
        'summary' => 'Fokus pada kompetensi.',
       'verified_gaps' => [
    [
        'skill' => 'Test Programming',
        'actual_level' => 2,
        'required_level' => 4,
        'priority' => 'high',
        'reason' => 'Level aktual masih di bawah persyaratan.',
        'actions' => ['Latihan pemrograman.'],
    ],
    [
        'skill' => 'Test Programming',
        'actual_level' => 2,
        'required_level' => 4,
        'priority' => 'high',
        'reason' => 'Level aktual masih di bawah persyaratan.',
        'actions' => ['Latihan pemrograman.'],
    ],
],
        'verification_needed' => [],
        'development_plan' => [],
    ];

    Http::fake([
        'api.groq.com/*' => Http::response([
            'choices' => [
                [
                    'message' => [
                        'content' => json_encode($response),
                    ],
                ],
            ],
        ], 200),
    ]);

    $this->expectException(\RuntimeException::class);
    $this->expectExceptionMessage(
        'AI tidak menyertakan seluruh kesenjangan kompetensi terverifikasi.'
    );

    app(AiRecommendationService::class)->generate(
        $this->createMatchingResult()
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
                    'message' => 'Simulated API error',
                ],
            ], 429),
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Permintaan Groq gagal dengan HTTP 429.'
        );

        app(AiRecommendationService::class)->generate(
            $this->createMatchingResult()
        );
    }
    
    public function test_generated_recommendation_is_saved_to_database(): void
    {
        config([
            'services.groq.key' => 'test-api-key',
            'services.groq.model' => 'test-model',
        ]);

        $expected = [
            'summary' => 'Fokus pada kompetensi yang masih memiliki GAP.',
            'verified_gaps' => [
    [
        'skill' => 'Test Programming',
        'actual_level' => 2,
        'required_level' => 4,
        'priority' => 'high',
        'reason' => 'Level aktual masih di bawah persyaratan.',
        'actions' => ['Latihan pemrograman secara rutin.'],
    ],
],
            'verification_needed' => [],
            'development_plan' => [],
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

        $matchingResult = $this->createMatchingResult();

        $recommendation = app(AiRecommendationService::class)
            ->generateAndSave($matchingResult);

        $this->assertSame('career_development', $recommendation->recommendation_type);
        $this->assertSame('completed', $recommendation->status);
        $this->assertSame('test-model', $recommendation->model_name);
        $this->assertSame($expected, json_decode($recommendation->content, true));
        $this->assertNotNull($recommendation->generated_at);

        $this->assertDatabaseHas('ai_recommendations', [
            'id' => $recommendation->id,
            'matching_result_id' => $matchingResult->id,
            'status' => 'completed',
        ]);
    }
    
    public function test_failed_generation_is_recorded_in_database(): void
    {
        config([
            'services.groq.key' => 'test-api-key',
            'services.groq.model' => 'test-model',
        ]);

        Http::fake([
            'api.groq.com/*' => Http::response([
                'error' => [
                    'message' => 'Simulated API error',
                ],
            ], 429),
        ]);

        $matchingResult = $this->createMatchingResult();

        try {
            app(AiRecommendationService::class)
                ->generateAndSave($matchingResult);

            $this->fail('Seharusnya proses rekomendasi gagal.');
        } catch (\RuntimeException $exception) {
            $this->assertSame(
                'Permintaan Groq gagal dengan HTTP 429.',
                $exception->getMessage()
            );
        }

        $this->assertDatabaseHas('ai_recommendations', [
            'matching_result_id' => $matchingResult->id,
            'recommendation_type' => 'career_development',
            'status' => 'failed',
            'model_name' => 'test-model',
        ]);

        $this->assertDatabaseMissing('ai_recommendations', [
            'matching_result_id' => $matchingResult->id,
            'status' => 'completed',
        ]);
        
    }
    
    public function test_unverified_skill_is_rejected_from_verified_gaps(): void
    {
        config([
            'services.groq.key' => 'test-api-key',
            'services.groq.model' => 'test-model',
        ]);

        $matchingResult = $this->createMatchingResult();

        $matchingResult->details()->first()->update([
            'actual_level' => null,
            'evaluation_status' => 'pending',
        ]);

        $responseData = [
            'summary' => 'Kompetensi perlu diverifikasi.',
            'verified_gaps' => [
                [
                    'skill' => 'Test Programming',
                    'actual_level' => 0,
                    'required_level' => 4,
                    'priority' => 'high',
                    'reason' => 'Level aktual belum diketahui.',
                    'actions' => ['Verifikasi kompetensi.'],
                ],
            ],
            'verification_needed' => [],
            'development_plan' => [],
        ];

        Http::fake([
            'api.groq.com/*' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => json_encode($responseData),
                        ],
                    ],
                ],
            ], 200),
        ]);

        try {
            app(AiRecommendationService::class)->generate(
                $matchingResult
            );

            $this->fail(
                'Kompetensi yang belum terverifikasi seharusnya ditolak.'
            );
        } catch (RuntimeException $exception) {
            $this->assertSame(
                'AI menghasilkan kesenjangan kompetensi yang tidak terverifikasi.',
                $exception->getMessage()
            );
        }
    }
    
    public function test_incorrect_skill_levels_from_ai_are_rejected(): void
    {
        config([
            'services.groq.key' => 'test-api-key',
            'services.groq.model' => 'test-model',
        ]);

        $matchingResult = $this->createMatchingResult();

        $responseData = [
            'summary' => 'Kompetensi perlu dikembangkan.',
            'verified_gaps' => [
                [
                    'skill' => 'Test Programming',
                    'actual_level' => 1,
                    'required_level' => 4,
                    'priority' => 'high',
                    'reason' => 'Level aktual berada di bawah persyaratan.',
                    'actions' => ['Latihan pemrograman secara rutin.'],
                ],
            ],
            'verification_needed' => [],
            'development_plan' => [],
        ];

        Http::fake([
            'api.groq.com/*' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => json_encode($responseData),
                        ],
                    ],
                ],
            ], 200),
        ]);

        try {
            app(AiRecommendationService::class)->generate(
                $matchingResult
            );

            $this->fail(
                'Level aktual yang berbeda dari database seharusnya ditolak.'
            );
        } catch (RuntimeException $exception) {
            $this->assertSame(
                'Level kompetensi dalam rekomendasi AI tidak sesuai dengan data Profile Matching.',
                $exception->getMessage()
            );
        }
    }
    
    public function test_invalid_recommendation_item_structure_is_rejected(): void
    {
        config([
            'services.groq.key' => 'test-api-key',
            'services.groq.model' => 'test-model',
        ]);

        $matchingResult = $this->createMatchingResult();

        $responseData = [
            'summary' => 'Kompetensi perlu dikembangkan.',
            'verified_gaps' => [
                [
                    'skill' => 'Test Programming',
                    'actual_level' => 2,
                    'required_level' => 4,
                    'priority' => 'high',
                    'reason' => 'Level aktual di bawah persyaratan.',
                    'actions' => 'Belajar pemrograman',
                ],
            ],
            'verification_needed' => [],
            'development_plan' => [],
        ];

        Http::fake([
            'api.groq.com/*' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => json_encode($responseData),
                        ],
                    ],
                ],
            ], 200),
        ]);

        try {
            app(AiRecommendationService::class)->generate(
                $matchingResult
            );

            $this->fail(
                'Struktur actions yang bukan array seharusnya ditolak.'
            );
        } catch (RuntimeException $exception) {
            $this->assertSame(
                'Struktur verified_gaps dari Groq tidak valid.',
                $exception->getMessage()
            );
        }
    }
public function test_completed_recommendation_is_not_generated_twice(): void
{
    $matchingResult = $this->createMatchingResult();

    $existing = $matchingResult->aiRecommendations()->create([
        'recommendation_type' => 'career_development',
        'content' => json_encode([
            'summary' => 'Existing recommendation',
            'verified_gaps' => [],
            'verification_needed' => [],
            'development_plan' => [],
        ]),
        'model_name' => 'test-model',
        'status' => 'completed',
        'generated_at' => now(),
    ]);

    config([
        'services.groq.key' => 'test-api-key',
        'services.groq.model' => 'test-model',
    ]);

    Http::fake();

    try {
        app(AiRecommendationService::class)
            ->generateAndSave($matchingResult);

        $this->fail('Seharusnya rekomendasi duplikat ditolak.');
    } catch (RuntimeException $exception) {
        $this->assertSame(
            'Rekomendasi untuk hasil analisis ini sudah tersedia.',
            $exception->getMessage()
        );
    }

    Http::assertNothingSent();

    $this->assertDatabaseCount('ai_recommendations', 1);

    $this->assertDatabaseHas('ai_recommendations', [
        'id' => $existing->id,
        'status' => 'completed',
    ]);
}
public function test_missing_verification_needed_is_rejected(): void
{
    config([
        'services.groq.key' => 'test-api-key',
        'services.groq.model' => 'test-model',
    ]);

    $matchingResult = $this->createMatchingResult();

    $matchingResult->details()->first()->update([
        'actual_level' => null,
        'evaluation_status' => 'pending',
    ]);

    $response = [
        'summary' => 'Kompetensi perlu diverifikasi.',
        'verified_gaps' => [],
        'verification_needed' => [],
        'development_plan' => [],
    ];

    Http::fake([
        'api.groq.com/*' => Http::response([
            'choices' => [
                [
                    'message' => [
                        'content' => json_encode($response),
                    ],
                ],
            ],
        ], 200),
    ]);

    $this->expectException(\RuntimeException::class);
    $this->expectExceptionMessage(
        'AI tidak menyertakan seluruh kompetensi yang membutuhkan verifikasi.'
    );

    app(AiRecommendationService::class)->generate($matchingResult);
}
public function test_duplicate_verification_needed_is_rejected(): void
{
    config([
        'services.groq.key' => 'test-api-key',
        'services.groq.model' => 'test-model',
    ]);

    $matchingResult = $this->createMatchingResult();

    $matchingResult->details()->first()->update([
        'actual_level' => null,
        'evaluation_status' => 'pending',
    ]);

    $response = [
        'summary' => 'Kompetensi perlu diverifikasi.',
        'verified_gaps' => [],
       'verification_needed' => [
    [
        'skill' => 'Test Programming',
        'status' => 'pending',
        'action' => 'Perlu verifikasi kompetensi.',
    ],
    [
        'skill' => 'Test Programming',
        'status' => 'pending',
        'action' => 'Perlu verifikasi kompetensi.',
    ],
],
        'development_plan' => [],
    ];

    Http::fake([
        'api.groq.com/*' => Http::response([
            'choices' => [
                [
                    'message' => [
                        'content' => json_encode($response),
                    ],
                ],
            ],
        ], 200),
    ]);

    $this->expectException(\RuntimeException::class);
    $this->expectExceptionMessage(
        'AI tidak menyertakan seluruh kompetensi yang membutuhkan verifikasi.'
    );

    app(AiRecommendationService::class)->generate($matchingResult);
}

public function test_successful_generation_updates_processing_record(): void
{
    config([
        'services.groq.key' => 'test-api-key',
        'services.groq.model' => 'test-model',
    ]);

    $expected = [
        'summary' => 'Fokus pada kompetensi yang masih memiliki GAP.',
        'verified_gaps' => [
            [
                'skill' => 'Test Programming',
                'actual_level' => 2,
                'required_level' => 4,
                'priority' => 'high',
                'reason' => 'Level aktual masih di bawah persyaratan.',
                'actions' => ['Latihan pemrograman secara rutin.'],
            ],
        ],
        'verification_needed' => [],
        'development_plan' => [],
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

    $matchingResult = $this->createMatchingResult();

    $recommendation = app(AiRecommendationService::class)
        ->generateAndSave($matchingResult);

    $this->assertDatabaseCount('ai_recommendations', 1);

    $this->assertDatabaseHas('ai_recommendations', [
        'id' => $recommendation->id,
        'matching_result_id' => $matchingResult->id,
        'status' => 'completed',
    ]);

    $this->assertSame(
        $expected,
        json_decode($recommendation->content, true)
    );

    $this->assertNotNull($recommendation->generated_at);
}

public function test_failed_generation_updates_processing_record(): void
{
    config([
        'services.groq.key' => 'test-api-key',
        'services.groq.model' => 'test-model',
    ]);

    Http::fake([
        'api.groq.com/*' => Http::response([
            'error' => [
                'message' => 'Simulated API error',
            ],
        ], 429),
    ]);

    $matchingResult = $this->createMatchingResult();

    try {
        app(AiRecommendationService::class)
            ->generateAndSave($matchingResult);

        $this->fail('Seharusnya proses rekomendasi gagal.');
    } catch (RuntimeException $exception) {
        $this->assertSame(
            'Permintaan Groq gagal dengan HTTP 429.',
            $exception->getMessage()
        );
    }

    $this->assertDatabaseCount('ai_recommendations', 1);

    $this->assertDatabaseHas('ai_recommendations', [
        'matching_result_id' => $matchingResult->id,
        'status' => 'failed',
        'content' => '{}',
        'model_name' => 'test-model',
    ]);

    $this->assertDatabaseMissing('ai_recommendations', [
        'matching_result_id' => $matchingResult->id,
        'status' => 'completed',
    ]);
}

}
