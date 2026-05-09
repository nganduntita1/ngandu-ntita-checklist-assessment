<?php

namespace App\Services;

use App\Models\ChecklistTemplate;
use App\Models\User;
use App\Repositories\Contracts\TemplateRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class TemplateService
{
    public function __construct(
        private readonly TemplateRepositoryInterface $templateRepository
    ) {}

    /**
     * Return a paginated list of templates, optionally filtered.
     */
    public function list(array $filters): LengthAwarePaginator
    {
        return $this->templateRepository->paginate($filters);
    }

    /**
     * Find a single template by ID.
     */
    public function find(int $id): ChecklistTemplate
    {
        return $this->templateRepository->findOrFail($id);
    }

    /**
     * Create a new template with its questions inside a DB transaction.
     *
     * @param  array{title: string, description?: string, status?: string, questions?: array}  $data
     */
    public function create(array $data, User $admin): ChecklistTemplate
    {
        return DB::transaction(function () use ($data, $admin) {
            $templateData = [
                'title'       => $data['title'],
                'description' => $data['description'] ?? null,
                'status'      => $data['status'] ?? 'active',
                'created_by'  => $admin->id,
            ];

            $template = $this->templateRepository->create($templateData);

            if (! empty($data['questions'])) {
                $this->templateRepository->syncQuestions($template, $data['questions']);
            }

            return $template->load('questions');
        });
    }

    /**
     * Update an existing template (and optionally its questions) inside a DB transaction.
     *
     * @param  array{title?: string, description?: string, status?: string, questions?: array}  $data
     */
    public function update(ChecklistTemplate $template, array $data): ChecklistTemplate
    {
        return DB::transaction(function () use ($template, $data) {
            // Only include fields that were explicitly provided in the payload
            $templateData = [];

            if (array_key_exists('title', $data)) {
                $templateData['title'] = $data['title'];
            }

            if (array_key_exists('description', $data)) {
                $templateData['description'] = $data['description'];
            }

            if (array_key_exists('status', $data)) {
                $templateData['status'] = $data['status'];
            }

            $updated = $this->templateRepository->update($template, $templateData);

            if (array_key_exists('questions', $data)) {
                $this->templateRepository->syncQuestions($updated, $data['questions'] ?? []);
            }

            return $updated->load('questions');
        });
    }

    /**
     * Delete a template.
     */
    public function delete(ChecklistTemplate $template): void
    {
        $this->templateRepository->delete($template);
    }
}
