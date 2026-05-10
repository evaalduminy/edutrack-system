<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Interfaces\ResearchRepositoryInterface;
use App\Jobs\ProcessThesisAI;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * ThesisUploadController
 * 
 * متحكم متخصص في استقبال الملفات وإرسالها للطابور لضمان تجربة مستخدم سريعة.
 */
class ThesisUploadController extends Controller
{
    public function __construct(
        protected ResearchRepositoryInterface $thesisRepository
    ) {}

    /**
     * @OA\Post(
     *     path="/api/v1/thesis/upload",
     *     summary="رفع ملف بحث جديد (Upload New Thesis)",
     *     description="يستقبل ملف PDF من المستخدم ويضعه في طابور المعالجة للذكاء الاصطناعي (Uploads a PDF and queues it for background AI processing).",
     *     operationId="uploadThesis",
     *     tags={"Thesis Management"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="title", type="string", description="عنوان البحث (Thesis Title)"),
     *                 @OA\Property(property="file", type="string", format="binary", description="ملف البحث بصيغة PDF حصراً")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=202,
     *         description="تم استلام الملف بنجاح (File accepted successfully)",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="تم استلام ملفك وجاري معالجته في الخلفية."),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="thesis_id", type="integer", example=1),
     *                 @OA\Property(property="status", type="string", example="pending")
     *             )
     *         )
     *     )
     * )
     *
     * استقبال الملف ودفعه للطابور.
     */
    public function upload(Request $request): JsonResponse
    {
        // 1. التحقق من صحة المدخلات (Validation)
        $request->validate([
            'title' => 'required|string|max:255',
            'file'  => 'required|file|mimes:pdf|max:20480', // الحد الأقصى 20 ميجابايت
        ]);

        $user = $request->user();

        // 2. حفظ الملف في المجلد المحلي (storage/app/theses)
        $path = $request->file('file')->store('theses', 'local');
        $absolutePath = storage_path('app/' . $path);

        // 3. إنشاء سجل مبدئي في قاعدة البيانات (الحالة الافتراضية pending)
        $thesis = $this->thesisRepository->create([
            'title'         => $request->title,
            'status'        => 'pending',
            'researcher_id' => $user->id,
            'department_id' => $user->department_id ?? null,
            // (في المشاريع الحقيقية قد نقوم بحفظ المسار أيضاً في الحقل المناسب)
        ]);

        // 4. السحر هنا: إرسال المهمة للطابور للعمل في الخلفية (Dispatch)
        ProcessThesisAI::dispatch($thesis->id, $absolutePath);

        // 5. إرجاع استجابة فورية للمستخدم (HTTP 202 Accepted) تجربة مستخدم سلسة
        return response()->json([
            'success' => true,
            'message' => 'تم استلام ملفك وجاري معالجته في الخلفية.',
            'data'    => [
                'thesis_id' => $thesis->id,
                'status'    => 'pending'
            ]
        ], 202);
    }
}
