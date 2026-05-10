<?php

namespace App\Services;

use App\Interfaces\ResearchRepositoryInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;
// نستخدم مكتبة Smalot بدلاً من Spatie لأنها نقية (Pure PHP)
// ولا تحتاج لتنصيب برامج معقدة (مثل Poppler) على نظام Windows
use Smalot\PdfParser\Parser; 

/**
 * PdfParsingService
 * 
 * خدمة مسؤولة عن قراءة نصوص ملفات PDF وإرسالها لخدمة الذكاء الاصطناعي
 * وتحديث قاعدة البيانات بالنتائج.
 */
class PdfParsingService
{
    public function __construct(
        protected ResearchRepositoryInterface $thesisRepository, // نستخدم Research/Thesis Repository
        protected Parser $pdfParser
    ) {}

    /**
     * استخراج النص من الـ PDF، تحليله بالذكاء الاصطناعي، وتحديث السجل.
     *
     * @param int $thesisId معرف البحث
     * @param string $absoluteFilePath المسار الكامل لملف الـ PDF
     * @return array مصفوفة تحتوي على حالة العملية
     */
    public function processAndAnalyze(int $thesisId, string $absoluteFilePath): array
    {
        try {
            // 1. استخراج النص الخام من الـ PDF
            $rawText = $this->extractTextFromPdf($absoluteFilePath);
            
            // 2. تنظيف النص من المسافات الزائدة والفراغات
            $cleanedText = $this->cleanText($rawText);

            // التحقق من أن النص ليس فارغاً (مثلاً لو كان الـ PDF عبارة عن صور فقط)
            if (empty($cleanedText)) {
                return $this->errorResponse('الملف لا يحتوي على نص قابل للقراءة (قد يكون صوراً).');
            }

            // 3. إرسال النص إلى خدمة FastAPI باستخدام Laravel HTTP Client
            // نستخدم المسار الموجود مسبقاً لاستخراج الميتا داتا
            $aiUrl = env('AI_SERVICE_URL', 'http://127.0.0.1:8001') . '/api/v1/extract-metadata';
            
            $response = Http::timeout(30) // وضع حد أقصى للانتظار 30 ثانية لتجنب توقف النظام
                ->post($aiUrl, [
                    'text' => $cleanedText
                ]);

            if ($response->failed()) {
                Log::error('AI Service Failed', ['status' => $response->status(), 'body' => $response->body()]);
                return $this->errorResponse('فشل الاتصال بخدمة الذكاء الاصطناعي.');
            }

            $aiData = $response->json('data');

            // 4. تحديث سجل البحث في قاعدة البيانات باستخدام الـ Repository
            // دمج الـ ai_metadata مع إضافة حقل لنسبة الثقة (Confidence Score)
            $metadataToSave = array_merge($aiData, [
                'confidence_score' => $this->calculateConfidenceScore($aiData),
                'parsed_at'        => now()->toIso8601String(),
            ]);

            $this->thesisRepository->update($thesisId, [
                'ai_metadata' => $metadataToSave
            ]);

            return [
                'success' => true,
                'message' => 'تم تحليل الملف وحفظ بيانات الذكاء الاصطناعي بنجاح.',
                'data'    => $metadataToSave
            ];

        } catch (Exception $e) {
            // معالجة الأخطاء (مثل الملفات المحمية بكلمة مرور أو التالفة)
            Log::error('PDF Parsing Error: ' . $e->getMessage());
            
            return $this->errorResponse(
                'تعذر قراءة الملف. قد يكون الملف تالفاً أو محمياً بكلمة مرور.',
                $e->getMessage()
            );
        }
    }

    /**
     * استخراج النص باستخدام مكتبة Smalot
     */
    private function extractTextFromPdf(string $filePath): string
    {
        if (!file_exists($filePath)) {
            throw new Exception("الملف غير موجود في المسار: {$filePath}");
        }

        // يقوم بتحليل الملف واستخراج النص الخام
        $pdf = $this->pdfParser->parseFile($filePath);
        return $pdf->getText();
    }

    /**
     * تنظيف النص المستخرج
     */
    private function cleanText(string $text): string
    {
        // استبدال أي مسافات زائدة أو أسطر متتالية بمسافة واحدة
        $text = preg_replace('/\s+/', ' ', $text);
        return trim($text);
    }

    /**
     * إرجاع مصفوفة الخطأ الموحدة
     */
    private function errorResponse(string $message, string $details = null): array
    {
        return [
            'success' => false,
            'message' => $message,
            'details' => $details
        ];
    }

    /**
     * حساب درجة الثقة (Confidence Score) بناءً على جودة الاستخراج
     */
    private function calculateConfidenceScore(array $aiData): float
    {
        // منطق مبسط: كلما استخرجنا كلمات مفتاحية وكيانات أكثر، زادت الثقة بالنص المقروء
        $score = 0.5; // نقطة بداية 50%
        
        if (!empty($aiData['keywords'])) {
            $score += 0.3; 
        }
        if (!empty($aiData['entities']['dates']) || !empty($aiData['entities']['emails'])) {
            $score += 0.15;
        }

        return min($score, 0.99); // الحد الأقصى 99%
    }
}
