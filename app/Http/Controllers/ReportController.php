<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReportFilterRequest;
use App\Http\Resources\ReportResource;
use App\Services\ReportService;
use Illuminate\Http\JsonResponse;

class ReportController extends Controller
{
    public function __construct(
        private readonly ReportService $reportService
    ) {}

    /**
     * Return a filtered, paginated report of all checklist instances.
     *
     * GET /api/reports
     */
    public function index(ReportFilterRequest $request): JsonResponse
    {
        $this->authorize('viewAny-report');

        $paginator = $this->reportService->list($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Report retrieved',
            'data'    => [
                'data' => ReportResource::collection($paginator->items()),
                'meta' => [
                    'current_page' => $paginator->currentPage(),
                    'last_page'    => $paginator->lastPage(),
                    'per_page'     => $paginator->perPage(),
                    'total'        => $paginator->total(),
                ],
            ],
        ], 200);
    }
}
