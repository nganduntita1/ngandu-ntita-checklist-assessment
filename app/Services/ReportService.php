<?php

namespace App\Services;

use App\Repositories\Contracts\ReportRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ReportService
{
    public function __construct(
        private readonly ReportRepositoryInterface $reportRepository
    ) {}

    /**
     * Return a paginated, filtered list of checklist instances for reporting.
     */
    public function list(array $filters): LengthAwarePaginator
    {
        return $this->reportRepository->paginate($filters);
    }
}
