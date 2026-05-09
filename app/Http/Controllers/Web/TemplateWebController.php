<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ChecklistTemplate;
use App\Services\TemplateService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TemplateWebController extends Controller
{
    public function __construct(
        private readonly TemplateService $templateService
    ) {}

    /**
     * Show the paginated template list.
     *
     * GET /templates
     */
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', ChecklistTemplate::class);

        $filters = $request->only(['search', 'page', 'per_page']);

        $paginator = $this->templateService->list($filters);

        return Inertia::render('Templates/Index', [
            'templates' => $paginator->items(),
            'meta'      => [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
            ],
            'filters' => $filters,
        ]);
    }

    /**
     * Show the template creation form.
     *
     * GET /templates/create
     */
    public function create(): Response
    {
        $this->authorize('create', ChecklistTemplate::class);

        return Inertia::render('Templates/Create');
    }

    /**
     * Show the template edit form.
     *
     * GET /templates/{template}/edit
     */
    public function edit(ChecklistTemplate $template): Response
    {
        $this->authorize('update', $template);

        $template->loadMissing('questions');

        return Inertia::render('Templates/Edit', [
            'template' => $template,
        ]);
    }
}
