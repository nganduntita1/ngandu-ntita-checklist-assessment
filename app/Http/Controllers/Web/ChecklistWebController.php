<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveDraftRequest;
use App\Models\ChecklistInstance;
use App\Services\ChecklistService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ChecklistWebController extends Controller
{
    public function __construct(
        private readonly ChecklistService $checklistService
    ) {}

    /**
     * Show the list of active templates for the auditor to pick from.
     *
     * GET /checklists/start
     */
    public function startIndex(Request $request): Response
    {
        $templates = \App\Models\ChecklistTemplate::where('status', 'active')
            ->withCount('questions')
            ->orderBy('title')
            ->get();

        return Inertia::render('Checklists/Start', [
            'templates' => $templates,
        ]);
    }

    /**
     * Create a new draft checklist instance from the chosen template.
     *
     * POST /checklists/start
     */
    public function start(Request $request): RedirectResponse
    {
        $request->validate([
            'template_id' => ['required', 'integer', 'exists:checklist_templates,id'],
        ]);

        $instance = $this->checklistService->start(
            (int) $request->input('template_id'),
            $request->user()
        );

        return redirect()->route('checklists.show', $instance)
            ->with('success', 'Checklist started. Fill in your answers below.');
    }

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

    /**
     * Save draft answers for a checklist instance.
     *
     * POST /checklists/{checklist}/save-draft
     */
    public function saveDraft(SaveDraftRequest $request, ChecklistInstance $checklist): RedirectResponse
    {
        $this->authorize('update', $checklist);

        $this->checklistService->saveDraft($checklist, $request->validated('answers', []));

        return redirect()->route('checklists.show', $checklist)
            ->with('success', 'Draft saved successfully.');
    }

    /**
     * Submit a completed checklist instance.
     *
     * POST /checklists/{checklist}/submit
     */
    public function submit(Request $request, ChecklistInstance $checklist): RedirectResponse
    {
        $this->authorize('update', $checklist);

        // Save any answers provided before attempting submission
        $answers = $request->input('answers', []);
        if (!empty($answers)) {
            $this->checklistService->saveDraft($checklist, $answers);
            // Reload the instance so the submit action sees the latest answers
            $checklist->refresh();
        }

        $this->checklistService->submit($checklist);

        return redirect()->route('checklists.show', $checklist)
            ->with('success', 'Checklist submitted successfully.');
    }
}
