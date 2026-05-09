<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveDraftRequest;
use App\Http\Resources\InstanceResource;
use App\Models\ChecklistInstance;
use App\Services\ChecklistService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChecklistController extends Controller
{
    public function __construct(
        private readonly ChecklistService $checklistService
    ) {}

    /**
     * List the authenticated auditor's checklist instances.
     *
     * GET /api/checklists
     */
    public function index(Request $request): JsonResponse
    {
        $paginator = $this->checklistService->listForAuditor($request->user());

        return response()->json([
            'success' => true,
            'message' => 'Checklists retrieved',
            'data'    => [
                'data' => InstanceResource::collection($paginator->items()),
                'meta' => [
                    'current_page' => $paginator->currentPage(),
                    'last_page'    => $paginator->lastPage(),
                    'per_page'     => $paginator->perPage(),
                    'total'        => $paginator->total(),
                ],
            ],
        ], 200);
    }

    /**
     * Start a new checklist instance from a template.
     *
     * POST /api/checklists/start
     */
    public function start(Request $request): JsonResponse
    {
        $request->validate([
            'template_id' => ['required', 'integer', 'exists:checklist_templates,id'],
        ]);

        $instance = $this->checklistService->start(
            $request->integer('template_id'),
            $request->user()
        );

        return response()->json([
            'success' => true,
            'message' => 'Checklist started',
            'data'    => new InstanceResource($instance),
        ], 201);
    }

    /**
     * Save draft answers for a checklist instance.
     *
     * POST /api/checklists/{checklist}/save-draft
     */
    public function saveDraft(SaveDraftRequest $request, ChecklistInstance $checklist): JsonResponse
    {
        $this->authorize('update', $checklist);

        $instance = $this->checklistService->saveDraft($checklist, $request->validated('answers'));

        return response()->json([
            'success' => true,
            'message' => 'Draft saved',
            'data'    => new InstanceResource($instance),
        ], 200);
    }

    /**
     * Submit a completed checklist instance.
     *
     * POST /api/checklists/{checklist}/submit
     */
    public function submit(Request $request, ChecklistInstance $checklist): JsonResponse
    {
        $this->authorize('update', $checklist);

        $instance = $this->checklistService->submit($checklist);

        return response()->json([
            'success' => true,
            'message' => 'Checklist submitted',
            'data'    => new InstanceResource($instance),
        ], 200);
    }
}
