<?php

namespace App\Repositories;

use App\Models\ChecklistInstance;
use App\Repositories\Contracts\ReportRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ReportRepository implements ReportRepositoryInterface
{
    /**
     * Return a paginated, filtered list of checklist instances for reporting.
     * Eager-loads auditor and template relationships.
     * Supports filters: date_from, date_to, template_id, auditor_id.
     * Results are sorted by created_at descending.
     */
    public function paginate(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = ChecklistInstance::with(['auditor', 'template'])
            ->orderBy('created_at', 'desc');

        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        if (!empty($filters['template_id'])) {
            $query->where('template_id', $filters['template_id']);
        }

        if (!empty($filters['auditor_id'])) {
            $query->where('auditor_id', $filters['auditor_id']);
        }

        return $query->paginate($perPage);
    }
}
