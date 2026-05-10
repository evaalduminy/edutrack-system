<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreResearchRequest;
use App\Http\Requests\UpdateResearchRequest;
use App\Http\Resources\ResearchResource;
use App\Models\ActivityLog;
use App\Services\ResearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Research Controller (API)
 *
 * RESTful API controller for managing research entries.
 * Follows Skinny Controller pattern — all business logic
 * is delegated to ResearchService via Dependency Injection.
 *
 * @group Research Management
 * @authenticated
 */
class ResearchController extends Controller
{
    public function __construct(
        protected ResearchService $researchService,
    ) {}

    /**
     * List all research (paginated, searchable, filterable).
     *
     * @queryParam search string Search by title or abstract. Example: ذكاء اصطناعي
     * @queryParam status string Filter by status. Example: approved
     * @queryParam department_id integer Filter by department.
     * @queryParam per_page integer Results per page. Example: 15
     *
     * @response 200 {"data": [...], "meta": {"current_page": 1, ...}}
     */
    public function index(Request $request): JsonResponse
    {
        $research = $this->researchService->list($request->all());

        return response()->json([
            'data' => ResearchResource::collection($research),
            'meta' => [
                'current_page' => $research->currentPage(),
                'last_page'    => $research->lastPage(),
                'per_page'     => $research->perPage(),
                'total'        => $research->total(),
            ],
        ]);
    }

    /**
     * Show a single research entry.
     *
     * @urlParam id integer required The research ID. Example: 1
     *
     * @response 200 {"data": {...}}
     * @response 404 {"message": "البحث غير موجود."}
     */
    public function show(int $id): JsonResponse
    {
        $research = $this->researchService->find($id);

        if (! $research) {
            return response()->json([
                'message' => 'البحث غير موجود.',
            ], 404);
        }

        return response()->json([
            'data' => new ResearchResource($research),
        ]);
    }

    /**
     * Create a new research entry.
     *
     * Accepts a file upload (PDF/DOC/DOCX). Background jobs will:
     * 1. Compute SHA-256 hash
     * 2. Generate QR code
     * 3. Extract AI metadata via FastAPI
     *
     * @response 201 {"message": "تم إنشاء البحث بنجاح.", "data": {...}}
     */
    public function store(StoreResearchRequest $request): JsonResponse
    {
        $file = $request->file('file');

        // Check for duplicate file
        if ($file) {
            $duplicate = $this->researchService->checkDuplicate($file);
            if ($duplicate) {
                return response()->json([
                    'message'   => 'تم اكتشاف ملف مطابق! هذا البحث موجود بالفعل.',
                    'duplicate' => new ResearchResource($duplicate),
                ], 409);
            }
        }

        $research = $this->researchService->create(
            $request->validated(),
            $file
        );

        ActivityLog::log(
            'created',
            $request->user()->id,
            $research::class,
            $research->id,
            ['title' => $research->title]
        );

        return response()->json([
            'message' => 'تم إنشاء البحث بنجاح. جارٍ معالجة الملف في الخلفية...',
            'data'    => new ResearchResource($research->load(['researcher', 'department'])),
        ], 201);
    }

    /**
     * Update a research entry.
     *
     * @urlParam id integer required The research ID.
     *
     * @response 200 {"message": "تم تحديث البحث بنجاح."}
     */
    public function update(UpdateResearchRequest $request, int $id): JsonResponse
    {
        $research = $this->researchService->find($id);

        if (! $research) {
            return response()->json([
                'message' => 'البحث غير موجود.',
            ], 404);
        }

        $this->researchService->update(
            $id,
            $request->validated(),
            $request->file('file')
        );

        ActivityLog::log(
            'updated',
            $request->user()->id,
            $research::class,
            $id,
            $request->validated()
        );

        return response()->json([
            'message' => 'تم تحديث البحث بنجاح.',
        ]);
    }

    /**
     * Delete a research entry.
     *
     * @urlParam id integer required The research ID.
     *
     * @response 200 {"message": "تم حذف البحث بنجاح."}
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $research = $this->researchService->find($id);

        if (! $research) {
            return response()->json([
                'message' => 'البحث غير موجود.',
            ], 404);
        }

        ActivityLog::log(
            'deleted',
            $request->user()->id,
            $research::class,
            $id,
            ['title' => $research->title]
        );

        $this->researchService->delete($id);

        return response()->json([
            'message' => 'تم حذف البحث بنجاح.',
        ]);
    }

    /**
     * Check if an uploaded file is a duplicate.
     *
     * @bodyParam file file required The file to check.
     *
     * @response 200 {"is_duplicate": false}
     * @response 200 {"is_duplicate": true, "existing_research": {...}}
     */
    public function checkDuplicate(Request $request): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'file'],
        ]);

        $duplicate = $this->researchService->checkDuplicate($request->file('file'));

        if ($duplicate) {
            return response()->json([
                'is_duplicate'      => true,
                'existing_research' => new ResearchResource($duplicate),
            ]);
        }

        return response()->json([
            'is_duplicate' => false,
        ]);
    }
}
