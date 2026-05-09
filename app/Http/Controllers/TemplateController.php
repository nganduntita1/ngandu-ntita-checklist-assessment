<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTemplateRequest;
use App\Http\Requests\UpdateTemplateRequest;
use App\Http\Resources\TemplateResource;
use App\Models\ChecklistTemplate;
use App\Services\TemplateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TemplateController extends Controller
{
    public function __construct(
        private readonly TemplateService $templateService
    ) {}

    /**
     * List templates (paginated, with optional search).
     * Admins see all; Auditors see only active templates — filtered in the service.
     *
     * GET /api/templates
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', ChecklistTemplate::class);

        // Auditors only see active templates
        $filters = $request->only(['search', 'page', 'per_page']);
        if ($request->user()->role === 'auditor') {
            $filters['status'] = 'active';
        }

        $paginator = $this->templateService->list($filters);

        return response()->json([
            'success' => true,
            'message' => 'Templates retrieved',
            'data'    => [
                'data' => TemplateResource::collection($paginator->items()),
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
     * Create a new template with its questions.
     *
     * POST /api/templates
     */
    public function store(StoreTemplateRequest $request): JsonResponse
    {
        $this->authorize('create', ChecklistTemplate::class);

        $template = $this->templateService->create($request->validated(), $request->user());

        return response()->json([
            'success' => true,
            'message' => 'Template created',
            'data'    => new TemplateResource($template),
        ], 201);
    }

    /**
     * Show a single template with its questions.
     *
     * GET /api/templates/{id}
     */
    public function show(ChecklistTemplate $template): JsonResponse
    {
        $this->authorize('view', $template);

        $template->loadMissing('questions');

        return response()->json([
            'success' => true,
            'message' => 'Template retrieved',
            'data'    => new TemplateResource($template),
        ], 200);
    }

    /**
     * Update an existing template and its questions.
     *
     * PUT /api/templates/{id}
     */
    public function update(UpdateTemplateRequest $request, ChecklistTemplate $template): JsonResponse
    {
        $this->authorize('update', $template);

        $updated = $this->templateService->update($template, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Template updated',
            'data'    => new TemplateResource($updated),
        ], 200);
    }

    /**
     * Delete a template (cascade-deletes questions).
     *
     * DELETE /api/templates/{id}
     */
    public function destroy(ChecklistTemplate $template): JsonResponse
    {
        $this->authorize('delete', $template);

        $this->templateService->delete($template);

        return response()->json([
            'success' => true,
            'message' => 'Template deleted',
            'data'    => null,
        ], 200);
    }
}
