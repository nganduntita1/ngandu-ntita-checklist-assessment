<?php

namespace App\Repositories;

use App\Models\ChecklistQuestion;
use App\Models\ChecklistTemplate;
use App\Repositories\Contracts\TemplateRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TemplateRepository implements TemplateRepositoryInterface
{
    /**
     * Return a paginated list of templates, optionally filtered by a search term.
     * Results are sorted by created_at descending.
     */
    public function paginate(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = ChecklistTemplate::query()->orderBy('created_at', 'desc');

        if (!empty($filters['search'])) {
            $query->where('title', 'like', '%' . $filters['search'] . '%');
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->paginate($perPage);
    }

    /**
     * Find a template by ID or throw a ModelNotFoundException.
     */
    public function findOrFail(int $id): ChecklistTemplate
    {
        return ChecklistTemplate::findOrFail($id);
    }

    /**
     * Create and persist a new template.
     */
    public function create(array $data): ChecklistTemplate
    {
        return ChecklistTemplate::create($data);
    }

    /**
     * Update an existing template with the given data.
     */
    public function update(ChecklistTemplate $template, array $data): ChecklistTemplate
    {
        $template->update($data);

        return $template->fresh();
    }

    /**
     * Delete a template from the database.
     */
    public function delete(ChecklistTemplate $template): void
    {
        $template->delete();
    }

    /**
     * Replace all questions for a template.
     * Deletes existing questions and bulk-inserts the new set.
     */
    public function syncQuestions(ChecklistTemplate $template, array $questions): void
    {
        $template->questions()->delete();

        if (!empty($questions)) {
            $rows = array_map(function (array $question) use ($template) {
                return array_merge($question, [
                    'template_id' => $template->id,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
            }, $questions);

            ChecklistQuestion::insert($rows);
        }
    }
}
