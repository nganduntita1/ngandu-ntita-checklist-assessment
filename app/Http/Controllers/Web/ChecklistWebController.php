<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ChecklistInstance;
use App\Services\ChecklistService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ChecklistWebController extends Controller
{
    public function __construct(
        private readonly ChecklistService $checklistService
    ) {}

    /**
     * Show the auditor's checklist instance list.
     *
     * GET /checklists
     */
    public function index(Request $request): Response
    {
        $paginator = $this->checklistService->listForAuditor($request->user());

        return Inertia::render('Checklists/Index', [
            'checklists' => $paginator->items(),
            'meta'       => [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
            ],
        ]);
    }

    /**
     * Show a single checklist instance for completion or viewing.
     *
     * GET /checklists/{checklist}
     */
    public function show(ChecklistInstance $checklist): Response
    {
        $this->authorize('view', $checklist);

        $checklist->loadMissing(['template.questions', 'answers']);

        return Inertia::render('Checklists/Show', [
            'checklist' => $checklist,
        ]);
    }
}
