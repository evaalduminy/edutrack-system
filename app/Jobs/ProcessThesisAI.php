<?php

namespace App\Jobs;

use App\Interfaces\ResearchRepositoryInterface;
use App\Services\PdfParsingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * ProcessThesisAI Job
 * 
 * مهمة تعمل في الخلفية لمعالجة ملفات الـ PDF والتواصل مع الذكاء الاصطناعي
 * وتحديث حالة البحث في قاعدة البيانات.
 */
class ProcessThesisAI implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $thesisId,
        public string $absoluteFilePath
    ) {}

    /**
     * Execute the job.
     */
    public function handle(
        PdfParsingService $pdfParsingService,
        ResearchRepositoryInterface $thesisRepository
    ): void {
        try {
            // 1. حساب بصمة الملف (SHA-256) للتأكد من عدم التلاعب والتكرار
            // نستخدم الدالة الأصلية في PHP لضمان سرعة خيالية بدون مكتبات إضافية
            $fileHash = hash_file('sha256', $this->absoluteFilePath);

            // 2. تحديث الحالة إلى processing وحفظ البصمة الأمنية
            $thesisRepository->update($this->thesisId, [
                'status'           => 'processing',
                'file_hash_sha256' => $fileHash // المتوافق مع حقول الـ Migration السابقة
            ]);

            // 3. الاستدعاء الذكي للخدمة (دون تعديل كود الـ Service)
            $result = $pdfParsingService->processAndAnalyze($this->thesisId, $this->absoluteFilePath);

            // 4. تحديث الحالة النهائية بناءً على نتيجة الخدمة
            if ($result['success'] ?? false) {
                // نجحت المعالجة
                $thesisRepository->update($this->thesisId, ['status' => 'verified']);
            } else {
                // الملف تالف، مشفر، أو فشل الاتصال بالذكاء الاصطناعي
                $thesisRepository->update($this->thesisId, ['status' => 'failed_parsing']);
                Log::warning('ProcessThesisAI Job: PDF parsing failed', ['thesis_id' => $this->thesisId, 'result' => $result]);
            }
        } catch (\Exception $e) {
            // معالجة الأخطاء غير المتوقعة (مثل فقدان الاتصال بقاعدة البيانات)
            $thesisRepository->update($this->thesisId, ['status' => 'failed_parsing']);
            Log::error('Job ProcessThesisAI Failed: ' . $e->getMessage());
            
            // نعيد رمي الخطأ ليتم تسجيله بشكل صحيح في جدول الأخطاء الخاص بالـ Queue
            throw $e;
        }
    }
}
