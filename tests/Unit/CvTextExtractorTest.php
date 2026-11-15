<?php

namespace Tests\Unit;

use App\Services\CvTextExtractor;
use RuntimeException;
use Tests\TestCase;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;

class CvTextExtractorTest extends TestCase
{
    public function test_missing_file_is_rejected(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('File CV tidak ditemukan.');

        app(CvTextExtractor::class)->extract(
            sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'cv-file-tidak-ada-12345.pdf',
            'pdf'
        );
    }

    public function test_unsupported_extension_is_rejected(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'cv-test-');

        try {
            $this->expectException(RuntimeException::class);
            $this->expectExceptionMessage('Format CV tidak didukung.');

            app(CvTextExtractor::class)->extract($path, 'txt');
        } finally {
            if (is_file($path)) {
                unlink($path);
            }
        }
    }

    public function test_empty_pdf_is_rejected(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'cv-test-');

        try {
            file_put_contents($path, '');

            $this->expectException(RuntimeException::class);

            app(CvTextExtractor::class)->extract($path, 'pdf');
        } finally {
            if (is_file($path)) {
                unlink($path);
            }
        }
    }
    
public function test_valid_docx_text_is_extracted(): void
{
    $path = tempnam(sys_get_temp_dir(), 'cv-test-') . '.docx';

    try {
        $phpWord = new PhpWord();
        $section = $phpWord->addSection();

        $section->addText('Nama: Budi Santoso');
        $section->addText('Keahlian: PHP dan Laravel');

        IOFactory::createWriter($phpWord, 'Word2007')->save($path);

        $result = app(\App\Services\CvTextExtractor::class)
            ->extract($path, 'docx');

        $this->assertStringContainsString(
            'Nama: Budi Santoso',
            $result['extracted_text']
        );

        $this->assertStringContainsString(
            'Keahlian: PHP dan Laravel',
            $result['extracted_text']
        );

        $this->assertSame(
            mb_strlen($result['extracted_text']),
            $result['character_count']
        );
    } finally {
        if (is_file($path)) {
            unlink($path);
        }
    }
}

}
