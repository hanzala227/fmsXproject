<?php

namespace App\Services\FrexoAi\Pipeline;

use Exception;
use Illuminate\Http\UploadedFile;
use Smalot\PdfParser\Parser;

class Stage1IngestClassify
{
    /**
     * Ingest and validate uploaded files, assign server IDs, extract text and page counts.
     *
     * @param  array<UploadedFile>  $files
     * @return array Ingested document records
     *
     * @throws Exception
     */
    public function ingest(array $files): array
    {
        $maxDocs = config('frexo_ai.max_documents', 10);
        $maxSizeKb = config('frexo_ai.max_file_size_kb', 15360);

        if (count($files) > $maxDocs) {
            throw new Exception("Maximum of $maxDocs documents allowed per extraction.");
        }

        $ingested = [];

        foreach ($files as $idx => $file) {
            if (! $file instanceof UploadedFile) {
                continue;
            }

            if ($file->getSize() > ($maxSizeKb * 1024)) {
                throw new Exception("File {$file->getClientOriginalName()} exceeds maximum size of ".($maxSizeKb / 1024).'MB.');
            }

            $serverDocId = 'doc_'.uniqid().'_'.($idx + 1);
            $filename = $file->getClientOriginalName();
            $ext = strtolower($file->getClientOriginalExtension());
            $mime = $file->getClientMimeType() ?: '';

            $docRecord = [
                'document_id' => $serverDocId,
                'filename' => $filename,
                'extension' => $ext,
                'mime' => $mime,
                'pages_total' => 1,
                'failed_pages' => [],
                'text_layer' => '',
                'images' => [],
                'is_readable' => true,
            ];

            if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                $base64 = base64_encode(file_get_contents($file->getPathname()));
                $docRecord['images'][] = [
                    'page' => 1,
                    'base64' => $base64,
                    'mime' => $mime ?: 'image/jpeg',
                ];
                $docRecord['text_layer'] = "[IMAGE DOCUMENT: $filename]";
            } elseif ($ext === 'pdf') {
                $extractedText = $this->extractPdfText($file->getPathname());
                $docRecord['text_layer'] = $extractedText['text'];
                $docRecord['pages_total'] = max(1, $extractedText['pages']);
                $docRecord['failed_pages'] = $extractedText['failed_pages'];

                // If digital text is empty, attempt pdftoppm image extraction
                if (empty(trim($extractedText['text'])) && is_executable('/usr/bin/pdftoppm')) {
                    $docRecord['images'] = $this->renderPdfPagesToImages($file->getPathname(), 2);
                }
            } elseif (in_array($ext, ['txt', 'csv'])) {
                $docRecord['text_layer'] = substr(file_get_contents($file->getPathname()), 0, 40000);
            }

            $ingested[] = $docRecord;
        }

        if (empty($ingested)) {
            throw new Exception('No valid readable documents were provided.');
        }

        return $ingested;
    }

    protected function extractPdfText(string $path): array
    {
        $text = '';
        $pages = 1;
        $failedPages = [];

        // 1. Try pdftotext
        if (is_executable('/usr/bin/pdftotext')) {
            try {
                $cmd = '/usr/bin/pdftotext -layout '.escapeshellarg($path).' - 2>/dev/null';
                $out = @shell_exec($cmd);
                if (! empty($out) && trim($out) !== '') {
                    $text = $out;
                }
            } catch (\Throwable $e) {
                // fallback
            }
        }

        // 2. Fallback Smalot parser to get page count and text if pdftotext was empty
        try {
            $parser = new Parser;
            $pdf = $parser->parseFile($path);
            $parsedPages = $pdf->getPages();
            $pages = count($parsedPages);
            if (empty(trim($text))) {
                $text = $pdf->getText();
            }
        } catch (\Throwable $e) {
            $failedPages[] = 1;
        }

        return [
            'text' => substr($text ?? '', 0, 40000),
            'pages' => $pages,
            'failed_pages' => $failedPages,
        ];
    }

    protected function renderPdfPagesToImages(string $path, int $maxPages = 2): array
    {
        $images = [];
        $tempPrefix = sys_get_temp_dir().'/pdf_scan_'.uniqid();
        $cmd = '/usr/bin/pdftoppm -png -r 150 -f 1 -l '.$maxPages.' '.escapeshellarg($path).' '.escapeshellarg($tempPrefix);
        @exec($cmd);

        $rendered = glob($tempPrefix.'-*.png');
        if (is_array($rendered)) {
            foreach ($rendered as $idx => $imgFile) {
                $images[] = [
                    'page' => $idx + 1,
                    'base64' => base64_encode(file_get_contents($imgFile)),
                    'mime' => 'image/png',
                ];
                @unlink($imgFile);
            }
        }

        return $images;
    }
}
