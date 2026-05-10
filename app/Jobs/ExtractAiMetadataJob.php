<?php

namespace App\Jobs;

use App\Models\Research;
use App\Services\AiIntegrationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Extract AI Metadata Job
 *
 * Background job that sends research text to the FastAPI AI microservice
 * for NLP processing, keyword extraction, and metadata generation.
 * Results are stored in the research's ai_metadata JSONB column.
 */
class ExtractAiMetadataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 180; // AI processing may take longer

    /**
     * The number of seconds to wait before retrying the job.
     */
    public int $backoff = 10;

    public function __construct(
        protected int $researchId,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(AiIntegrationService $aiService): void
    {
        Log::info('ExtractAiMetadataJob started', [
            'research_id' => $this->researchId,
        ]);

        try {
            $research = Research::findOrFail($this->researchId);

            // Combine title and abstract for NLP processing
            $text = $research->title . "\n\n" . ($research->abstract ?? '');

            // Check if AI service is available
            if (! $aiService->healthCheck()) {
                Log::warning('AI service unavailable, will retry', [
                    'research_id' => $this->researchId,
                ]);
                $this->release(30); // Retry after 30 seconds
                return;
            }

            // Extract metadata
            $metadata = $aiService->extractMetadata($text);

            // Extract keywords
            $keywords = $aiService->extractKeywords($text);

            // Build the AI metadata object
            $aiMetadata = [
                'extracted_at' => now()->toIso8601String(),
                'metadata'     => $metadata,
                'keywords'     => $keywords,
                'language'     => $metadata['language'] ?? 'unknown',
                'word_count'   => str_word_count($text),
                'char_count'   => strlen($text),
            ];

            // Update the research record
            $research->update([
                'ai_metadata' => $aiMetadata,
            ]);

            Log::info('AI metadata extracted successfully', [
                'research_id'    => $this->researchId,
                'keywords_count' => count($keywords),
            ]);
        } catch (\Exception $e) {
            Log::error('AI metadata extraction failed', [
                'research_id' => $this->researchId,
                'error'       => $e->getMessage(),
            ]);

            // Don't re-throw — AI metadata is non-critical
            // Just log and move on
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::warning('ExtractAiMetadataJob permanently failed (non-critical)', [
            'research_id' => $this->researchId,
            'error'       => $exception->getMessage(),
        ]);
    }
}
