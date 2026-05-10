<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ArchiveResearchRequest;
use App\Http\Resources\ArchiveResource;
use App\Models\ActivityLog;
use App\Services\ArchiveService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Archive Controller (API)
 *
 * RESTful API controller for the archiving system.
 * Skinny controller — delegates to ArchiveService.
 *
 * @group Archive Management
 * @authenticated
 */
class ArchiveController extends Controller
{
    public function __construct(
        protected ArchiveService $archiveService,
    ) {}

    /**
     * List all archive records (paginated).
     *
     * @queryParam search string Search by archive number, notes, or research title.
     * @queryParam per_page integer Results per page. Example: 15
     *
     * @response 200 {"data": [...], "meta": {...}}
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 15);

        if ($request->has('search')) {
            $archives = $this->archiveService->search($request->input('search'), $perPage);
        } else {
            $archives = $this->archiveService->list($perPage);
        }

        return response()->json([
            'data' => ArchiveResource::collection($archives),
            'meta' => [
                'current_page' => $archives->currentPage(),
                'last_page'    => $archives->lastPage(),
                'per_page'     => $archives->perPage(),
                'total'        => $archives->total(),
            ],
        ]);
    }

    /**
     * Archive an approved research.
     *
     * @bodyParam research_id integer required The ID of the approved research.
     * @bodyParam notes string optional Notes about the archiving.
     *
     * @response 201 {"message": "تم أرشفة البحث بنجاح.", "data": {...}}
     * @response 422 {"message": "Only approved research can be archived."}
     */
    public function store(ArchiveResearchRequest $request): JsonResponse
    {
        try {
            $archiveRecord = $this->archiveService->archiveResearch(
                $request->validated()['research_id'],
                $request->user()->id,
                $request->validated()['notes'] ?? null
            );

            ActivityLog::log(
                'archived',
                $request->user()->id,
                $archiveRecord::class,
                $archiveRecord->id,
                ['archive_number' => $archiveRecord->archive_number]
            );

            return response()->json([
                'message' => 'تم أرشفة البحث بنجاح.',
                'data'    => new ArchiveResource($archiveRecord->load(['research', 'archivedBy'])),
            ], 201);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Find an archive record by archive number.
     *
     * @urlParam archiveNumber string required The archive number. Example: EDU-2026-CS-00001
     *
     * @response 200 {"data": {...}}
     * @response 404 {"message": "السجل غير موجود."}
     */
    public function show(string $archiveNumber): JsonResponse
    {
        $record = $this->archiveService->findByArchiveNumber($archiveNumber);

        if (! $record) {
            return response()->json([
                'message' => 'سجل الأرشيف غير موجود.',
            ], 404);
        }

        return response()->json([
            'data' => new ArchiveResource($record),
        ]);
    }
}
