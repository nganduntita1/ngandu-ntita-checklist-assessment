<?php

namespace App\Repositories\Contracts;

use App\Models\ChecklistInstance;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface InstanceRepositoryInterface
{
    public function create(array $data): ChecklistInstance;

    public function findOrFail(int $id): ChecklistInstance;

    public function upsertAnswers(ChecklistInstance $instance, array $answers): void;

    public function markCompleted(ChecklistInstance $instance): ChecklistInstance;
}
