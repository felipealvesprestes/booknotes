<?php

namespace App\Services\Pdf;

use App\Exceptions\PdfImportException;
use Illuminate\Support\Str;
use Smalot\PdfParser\Parser;

class PdfTextExtractor
{
    public function __construct(
        protected Parser $parser,
    ) {
    }

    /**
     * @return array{text: string, pages: int}
     */
    public function extract(string $path, int $maxPages = 30, int $maxCharacters = 8000): array
    {
        try {
            $document = $this->parser->parseFile($path);
        } catch (\Throwable $exception) {
            throw new PdfImportException(
                __('pdf_flashcards.errors.parse_failed'),
                $exception->getCode(),
                $exception,
            );
        }

        $pages = $document->getPages();
        $pageCount = is_array($pages) ? count($pages) : iterator_count($pages);

        if ($maxPages > 0 && $pageCount > $maxPages) {
            throw new PdfImportException(
                __('pdf_flashcards.errors.too_many_pages', [
                    'max' => $maxPages,
                    'pages' => $pageCount,
                ]),
            );
        }

        $text = trim($document->getText() ?? '');
        $text = preg_replace('/\\s+/u', ' ', $text) ?? '';

        if ($text === '') {
            throw new PdfImportException(__('pdf_flashcards.errors.empty_text'));
        }

        $normalized = Str::of($text)->squish();

        if ($maxCharacters > 0) {
            $normalized = $normalized->limit($maxCharacters, '...');
        }

        return [
            'text' => $normalized->value(),
            'pages' => $pageCount,
        ];
    }
}
