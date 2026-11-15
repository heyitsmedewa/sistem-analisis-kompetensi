<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class GeminiCvExtractor
{
    
private function isValidExperienceDate(string $date): bool
{
    if (preg_match('/^\d{4}$/', $date)) {
        return true;
    }

    if (preg_match('/^\d{4}-\d{2}$/', $date)) {
        [$year, $month] = array_map(
            'intval',
            explode('-', $date)
        );

        return checkdate($month, 1, $year);
    }

    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        [$year, $month, $day] = array_map(
            'intval',
            explode('-', $date)
        );

        return checkdate($month, $day, $year);
    }

    return false;
}

    public function extract(string $cvText): array
    {
        $apiKey = config('services.groq.key');
        $model = config('services.groq.model');

        if (empty($apiKey) || empty($model)) {
            throw new RuntimeException(
                'Konfigurasi Groq belum lengkap.'
            );
        }

        if (trim($cvText) === '') {
            throw new RuntimeException(
                'Teks CV tidak boleh kosong.'
            );
        }
    
        
        $prompt = <<<'PROMPT'
Ekstrak informasi CV menjadi JSON dengan struktur berikut:
{
  "personal_info": {},
  "profile_summary": null,
  "educations": [],
  "experiences": [],
  "hard_skills": [],
  "soft_skills": [],
  "languages": [],
  "organizations": [],
  "certifications": [],
  "projects": [],
  "achievements": [],
  "missing_or_uncertain": []
}

Struktur item:
- personal_info: objek dengan properti full_name, email, phone, address, linkedin_url, portfolio_url.
- educations: array objek dengan institution, degree, study_program, field_of_study, start_year, end_year, description.
- experiences: array objek dengan organization, position, employment_type, start_date, end_date, is_current, description.
- hard_skills: array string berisi keterampilan teknis yang tercantum di CV.
- soft_skills: array string berisi keterampilan nonteknis yang tercantum di CV.
- languages: array objek dengan name dan proficiency.
- organizations: array objek dengan name, position, start_date, end_date, description.
- certifications: array objek dengan name, issuer, issued_at, expires_at, credential_url.
- projects: array objek dengan name, description, role, project_url, started_at, completed_at.
- achievements: array objek dengan name, issuer, date, description.
- missing_or_uncertain: array string berisi informasi yang tidak jelas atau perlu dikonfirmasi.

Aturan:
- Gunakan hanya informasi yang benar-benar tercantum dalam CV.
- Jangan mengarang atau menyimpulkan kompetensi tanpa bukti.
- Gunakan null untuk properti objek yang tidak diketahui.
- Gunakan [] jika tidak ada item dalam suatu bagian array.
- Setiap item array harus mengikuti struktur yang telah ditentukan.
- start_year dan end_year harus berupa tahun empat digit atau null.
- Tanggal lain gunakan format YYYY-MM-DD jika lengkap, YYYY-MM jika hanya bulan dan tahun, atau YYYY jika hanya tahun. Jika tidak diketahui, gunakan null.
- is_current harus berupa boolean true atau false.
- hard_skills dan soft_skills harus berupa array string, bukan objek.
- Jangan menetapkan tingkat kompetensi numerik berdasarkan perkiraan AI.
- Pertahankan informasi yang tidak pasti di missing_or_uncertain.
- Sertakan seluruh 12 properti tingkat atas persis seperti struktur JSON.
- Balas hanya dengan JSON valid, tanpa markdown atau teks tambahan.
PROMPT;


        $response = Http::withOptions([
        'verify' => 'C:/xampp/apache/bin/curl-ca-bundle.crt',
    ])
    ->retry(
        2,
        20000,
        function ($exception, $request) {
            return $exception instanceof
                \Illuminate\Http\Client\RequestException
                && $exception->response->status() === 429;
        },
        throw: false
    )
    ->timeout(60)
    ->withToken($apiKey)
            ->post(
                'https://api.groq.com/openai/v1/chat/completions',
                [
                    'model' => $model,
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => $prompt,
                        ],
                        [
                            'role' => 'user',
                            'content' => "Teks CV:\n\n" . $cvText,
                        ],
                    ],
                    'response_format' => [
                        'type' => 'json_object',
                    ],
                    'max_tokens' => 4096,
                    'temperature' => 0.1,
                ]
            );

        if ($response->failed()) {
    \Illuminate\Support\Facades\Log::error(
        'Groq API request failed.',
        [
            'status' => $response->status(),
            'error' => $response->json('error') ?? $response->body(),
        ]
    );

    throw new RuntimeException(
        'Permintaan Groq gagal dengan HTTP '
            . $response->status() . '.'
    );
}

        $text = $response->json(
            'choices.0.message.content'
        );

        if (! is_string($text) || trim($text) === '') {
            throw new RuntimeException(
                'Groq tidak menghasilkan JSON.'
            );
        }

        $data = json_decode($text, true);

        if (! is_array($data) || json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException(
                'Respons Groq bukan JSON valid.'
            );
        }

        $requiredKeys = [
            'personal_info',
            'profile_summary',
            'educations',
            'experiences',
            'hard_skills',
            'soft_skills',
            'languages',
            'organizations',
            'certifications',
            'projects',
            'achievements',
            'missing_or_uncertain',
        ];

        foreach ($requiredKeys as $key) {
            if (! array_key_exists($key, $data)) {
                throw new RuntimeException(
                    'Struktur JSON Groq tidak lengkap: ' . $key
                );
            }
        }
        
$stringArrayKeys = [
    'hard_skills',
    'soft_skills',
    'missing_or_uncertain',
];


$objectArrayKeys = [
    'educations',
    'experiences',
    'languages',
    'organizations',
    'certifications',
    'projects',
    'achievements',
];


if (! is_array($data['personal_info']) || array_is_list($data['personal_info'])) {
    throw new RuntimeException(
        'Struktur JSON Groq tidak valid: personal_info harus berupa objek.'
    );
}


foreach ($objectArrayKeys as $key) {
    if (! is_array($data[$key]) || ! array_is_list($data[$key])) {
        throw new RuntimeException(
            'Struktur JSON Groq tidak valid: '
                . $key . ' harus berupa array objek.'
        );
    }

    foreach ($data[$key] as $item) {
        if (! is_array($item) || $item === [] || array_is_list($item)) {
            throw new RuntimeException(
                'Struktur JSON Groq tidak valid: '
                    . $key . ' harus berupa array objek.'
            );
        }
    }
    
if ($key === 'experiences') {
    foreach ($data[$key] as $item) {
        if (
            array_key_exists('is_current', $item)
            && ! is_bool($item['is_current'])
        ) {
            throw new RuntimeException(
                'Struktur JSON Groq tidak valid: experiences.is_current harus berupa boolean.'
            );
        }
    }
}


foreach ($data['experiences'] as $item) {
    foreach (['start_date', 'end_date'] as $dateKey) {
        $date = $item[$dateKey] ?? null;

        if (
            $date !== null
            && (
                ! is_string($date)
                || ! preg_match(
                    '/^\d{4}(?:-\d{2}(?:-\d{2})?)?$/',
                    $date
                )
                || ! $this->isValidExperienceDate($date)
            )
        ) {
            throw new RuntimeException(
                "Struktur JSON Groq tidak valid: experiences.{$dateKey} harus berupa tanggal YYYY-MM-DD, YYYY-MM, YYYY, atau null."
            );
        }
    }
}

    
if ($key === 'educations') {
    foreach ($data[$key] as $item) {
        foreach (['start_year', 'end_year'] as $yearKey) {
            $year = $item[$yearKey] ?? null;

            if (
                $year !== null
                && (
                    ! is_string($year) && ! is_int($year)
                    || ! preg_match('/^\d{4}$/', (string) $year)
                )
            ) {
                throw new RuntimeException(
                    'Struktur JSON Groq tidak valid: educations.'
                        . $yearKey . ' harus berupa tahun empat digit atau null.'
                );
            }
        }
    }
}

}

foreach ($stringArrayKeys as $key) {
    if (! is_array($data[$key]) || ! array_is_list($data[$key])) {
        throw new RuntimeException(
            'Struktur JSON Groq tidak valid: '
                . $key . ' harus berupa array string.'
        );
    }

    foreach ($data[$key] as $value) {
        if (! is_string($value) || trim($value) === '') {
            throw new RuntimeException(
                'Struktur JSON Groq tidak valid: '
                    . $key . ' harus berupa array string.'
            );
        }
    }
}

if (
    $data['profile_summary'] !== null
    && ! is_string($data['profile_summary'])
) {
    throw new RuntimeException(
        'Struktur JSON Groq tidak valid: profile_summary harus berupa string atau null.'
    );
}


        return $data;
    }
}
