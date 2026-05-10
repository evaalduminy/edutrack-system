<?php

namespace App\Jobs;

use App\Models\ResearchDocument;
use App\Services\QrCodeService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Generate QR Code Job
 *
 * Background job that generates a QR code for a research document.
 * The QR code encodes the document's access URL or archive number.
 */
class GenerateQrCodeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 60;

    public function __construct(
        protected int $researchId,
        protected int $documentId,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(QrCodeService $qrCodeService): void
    {
        Log::info('GenerateQrCodeJob started', [
            'research_id' => $this->researchId,
            'document_id' => $this->documentId,
        ]);

        try {
            $document = ResearchDocument::findOrFail($this->documentId);

            // Generate QR code with the document's access information
            $qrData = json_encode([
                'research_id' => $this->researchId,
                'document_id' => $this->documentId,
                'hash'        => $document->sha256_hash,
                'url'         => config('app.url') . '/api/v1/research/' . $this->researchId,
            ]);

            $filename = "research_{$this->researchId}_doc_{$this->documentId}.svg";
            $qrPath = $qrCodeService->generate($qrData, $filename);

            // Update document record with QR code path
            $document->update(['qr_code_path' => $qrPath]);

            Log::info('QR code generated successfully', [
                'research_id' => $this->researchId,
                'qr_path'     => $qrPath,
            ]);
        } catch (\Exception $e) {
            Log::error('QR code generation failed', [
                'research_id' => $this->researchId,
                'error'       => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::critical('GenerateQrCodeJob permanently failed', [
            'research_id' => $this->researchId,
            'document_id' => $this->documentId,
            'error'       => $exception->getMessage(),
        ]);
    }
}
