<?php

namespace App\Repositories\Contracts;

use App\Models\ChecklistTemplate;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface TemplateRepositoryInterface
{
    public function paginate(array $filters, int $perPage = 15): LengthAwarePaginator;

    public function findOrFail(int $id): ChecklistTemplate;

    public function create(array $data): ChecklistTemplate;

    public function update(ChecklistTemplate $template, array $data): ChecklistTemplate;

    public function delete(ChecklistTemplate $template): void;

    public function syncQuestions(ChecklistTemplate $template, array $questions): void;
}
