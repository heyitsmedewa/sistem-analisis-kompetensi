<?php

namespace App\Services;

use PhpOffice\PhpWord\Element\Text;
use PhpOffice\PhpWord\Element\TextRun;
use PhpOffice\PhpWord\IOFactory;
use Smalot\PdfParser\Parser;
use RuntimeException;
use Throwable;

class CvTextExtractor
{
    public function extract(string $path, string $extension): array
    {
        if (!is_file($path)) {
            throw new RuntimeException('File CV tidak ditemukan.');
        }

        $extension = strtolower($extension);

        
try {
    $text = match ($extension) {
        'pdf' => (new Parser())->parseFile($path)->getText(),
        'docx' => $this->extractDocx($path),
        default => throw new RuntimeException(
            'Format CV tidak didukung.'
        ),
    };
} catch (RuntimeException $e) {
    throw $e;
} catch (Throwable $e) {
    throw new RuntimeException(
        'Dokumen CV gagal dibaca.',
        0,
        $e
    );
}

        $text = preg_replace('/[ \t]+/u', ' ', $text);
        $text = preg_replace('/\R{3,}/u', "\n\n", $text);
        $text = trim($text);

        if ($text === '') {
            throw new RuntimeException(
                'Teks tidak berhasil ditemukan dalam dokumen.'
            );
        }

        return [
            'source' => $extension . '_parser',
            'extracted_text' => $text,
            'character_count' => mb_strlen($text),
        ];
    }

    private function extractDocx(string $path): string
    {
        $document = IOFactory::load($path);
        $lines = [];

        foreach ($document->getSections() as $section) {
            foreach ($section->getElements() as $element) {
                $text = $this->readElement($element);

                if ($text !== '') {
                    $lines[] = $text;
                }
            }
        }

        return implode("\n", $lines);
    }

    private function readElement(object $element): string
    {
        if ($element instanceof Text) {
            return (string) $element->getText();
        }

        if ($element instanceof TextRun) {
            $parts = [];

            foreach ($element->getElements() as $child) {
                $text = $this->readElement($child);

                if ($text !== '') {
                    $parts[] = $text;
                }
            }

            return implode('', $parts);
        }

        if (method_exists($element, 'getElements')) {
            $parts = [];

            foreach ($element->getElements() as $child) {
                $text = $this->readElement($child);

                if ($text !== '') {
                    $parts[] = $text;
                }
            }

            return implode(' ', $parts);
        }

        if (method_exists($element, 'getText')) {
            $value = $element->getText();

            if (is_string($value) || is_numeric($value)) {
                return (string) $value;
            }

            if (is_object($value)) {
                return $this->readElement($value);
            }
        }

        return '';
    }
}