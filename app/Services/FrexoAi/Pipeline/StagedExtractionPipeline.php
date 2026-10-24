<?php

namespace App\Services\FrexoAi\Pipeline;

use App\Services\FrexoAi\Client\GeminiClient;
use App\Services\FrexoAi\Client\GeminiClientInterface;
use App\Services\FrexoAi\DTO\ExtractionResultDTO;
use App\Services\FrexoAi\Mapper\FormDataMapper;
use Exception;

class StagedExtractionPipeline
{
    protected Stage1IngestClassify $stage1;
    protected Stage2PerDocumentExtractor $stage2;
    protected Stage3Reconciler $stage3;
    protected Stage4AntiHallucinationVerifier $stage4;

    public function __construct(
        ?GeminiClientInterface $client = null,
        ?Stage1IngestClassify $stage1 = null,
        ?Stage2PerDocumentExtractor $stage2 = null,
        ?Stage3Reconciler $stage3 = null,
        ?Stage4AntiHallucinationVerifier $stage4 = null
    ) {
        $geminiClient = $client ?? new GeminiClient();
        $this->stage1 = $stage1 ?? new Stage1IngestClassify();
        $this->stage2 = $stage2 ?? new Stage2PerDocumentExtractor($geminiClient);
        $this->stage3 = $stage3 ?? new Stage3Reconciler($geminiClient);
        $this->stage4 = $stage4 ?? new Stage4AntiHallucinationVerifier();
    }

    /**
     * Run the complete 4-stage extraction pipeline.
     *
     * @param array $files
     * @param string $module
     * @return ExtractionResultDTO
     * @throws Exception
     */
    public function execute(array $files, string $module): ExtractionResultDTO
    {
        $dto = new ExtractionResultDTO($module);

        // Stage 1: Ingest & Classify
        $ingestedDocs = $this->stage1->ingest($files);

        // Stage 2: Per-document Extraction
        $stage2Result = $this->stage2->extractAll($module, $ingestedDocs);
        foreach ($stage2Result['analyses'] as $analysis) {
            $dto->addDocumentAnalysis($analysis);
        }

        // Stage 3: Reconciliation
        $dto = $this->stage3->reconcile($module, $stage2Result['document_results'], $ingestedDocs, $dto);

        // Stage 4: Anti-Hallucination Verification
        $dto = $this->stage4->verify($dto, $ingestedDocs);

        // Map final form data to application structure (stripping any internal workflow fields)
        $dto->formData = FormDataMapper::mapToApplicationStructure($module, $dto->formData);

        return $dto;
    }
}
