<?php

namespace App\Services;

use App\Actions\SubmitChecklistAction;
use App\Models\ChecklistInstance;
use App\Models\User;
use App\Repositories\Contracts\InstanceRepositoryInterface;
use App\Repositories\Contracts\TemplateRepositoryInterface;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ChecklistService
{
    public function __construct(
        private readonly InstanceRepositoryInterface $instanceRepository,
        private readonly TemplateRepositoryInterface $templateRepository,
        private readonly SubmitChecklistAction $submitChecklistAction
    ) {}

    /**
     * Return a paginated list of checklist instances belonging to the given auditor.
     */
    public function listForAuditor(User $auditor): LengthAwarePaginator
    {
        return ChecklistInstance::with(['template', 'answers'])
            ->where('auditor_id', $auditor->id)
            ->orderBy('created_at', 'desc')
            ->paginate(15);
    }

    /**
     * Create a new draft checklist instance for the given auditor and template.
     */
    public function start(int $templateId, User $auditor): ChecklistInstance
    {
        // Ensure the template exists
        $this->templateRepository->findOrFail($templateId);

        $instance = $this->instanceRepository->create([
            'template_id' => $templateId,
            'auditor_id'  => $auditor->id,
            'status'      => 'draft',
        ]);

        return $instance->load(['template.questions', 'answers']);
    }

    /**
     * Persist draft answers for a checklist instance.
     *
     * @throws AuthorizationException  when the instance is already completed
     */
    public function saveDraft(ChecklistInstance $instance, array $answers): ChecklistInstance
    {
        if ($instance->status === 'completed') {
            throw new AuthorizationException('Cannot update answers on a completed checklist.');
        }

        $this->instanceRepository->upsertAnswers($instance, $answers);

        return $instance->load(['template.questions', 'answers']);
    }

    /**
     * Validate required answers and mark the instance as completed.
     *
     * @throws \Illuminate\Validation\ValidationException  when required questions are unanswered
     * @throws AuthorizationException                      when the instance is already completed
     */
    public function submit(ChecklistInstance $instance): ChecklistInstance
    {
        if ($instance->status === 'completed') {
            throw new AuthorizationException('Checklist has already been submitted.');
        }

        // Delegate required-answer validation to the action
        $this->submitChecklistAction->execute($instance);

        $completed = $this->instanceRepository->markCompleted($instance);

        return $completed->load(['template.questions', 'answers']);
    }
}
