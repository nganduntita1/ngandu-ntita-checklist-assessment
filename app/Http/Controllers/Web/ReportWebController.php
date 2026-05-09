<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReportFilterRequest;
use App\Models\ChecklistTemplate;
use App\Models\User;
use App\Services\ReportService;
use Inertia\Inertia;
use Inertia\Response;

class ReportWebController extends Controller
{
    public function __construct(
        private readonly ReportService $reportService
    ) {}

    /**
     * Show the filtered report table.
     *
     * GET /reports
     */
    public function index(ReportFilterRequest $request): Response
    {
        $this->authorize('viewAny-report');

        $paginator = $this->reportService->list($request->validated());

        $templates = ChecklistTemplate::orderBy('title')
            ->get(['id', 'title'])
            ->map(fn ($t) => ['id' => $t->id, 'title' => $t->title]);

        $auditors = User::where('role', 'auditor')
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($u) => ['id' => $u->id, 'name' => $u->name]);

        return Inertia::render('Reports/Index', [
            'reports'   => $paginator->items(),
            'meta'      => [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
            ],
            'filters'   => $request->validated(),
            'templates' => $templates,
            'auditors'  => $auditors,
        ]);
    }
}
