<?php

use App\Actions\SubmitChecklistAction;
use App\Models\ChecklistAnswer;
use App\Models\ChecklistInstance;
use App\Models\ChecklistQuestion;
use App\Models\ChecklistTemplate;
use App\Models\User;
use App\Repositories\Contracts\InstanceRepositoryInterface;
use App\Repositories\Contracts\TemplateRepositoryInterface;
use App\Services\ChecklistService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------

function makeChecklistService(
    InstanceRepositoryInterface $instanceRepo,
    TemplateRepositoryInterface $templateRepo,
    SubmitChecklistAction $action
): ChecklistService {
    return new ChecklistService($instanceRepo, $templateRepo, $action);
}

// ---------------------------------------------------------------------------
// submit() — throws ValidationException when required answers are missing
// ---------------------------------------------------------------------------

describe('ChecklistService::submit', function () {

    it('throws ValidationException when required answers are missing', function () {
        // Create a draft instance with one required question and no answers
        $template = ChecklistTemplate::factory()->create();
        $question = ChecklistQuestion::factory()->create([
            'template_id' => $template->id,
            'required'    => true,
        ]);
        $auditor  = User::factory()->auditor()->create();
        $instance = ChecklistInstance::factory()->draft()->create([
            'template_id' => $template->id,
            'auditor_id'  => $auditor->id,
        ]);

        // No answers created — required question is unanswered

        $instanceRepo = Mockery::mock(InstanceRepositoryInterface::class);
        $templateRepo = Mockery::mock(TemplateRepositoryInterface::class);
        $action       = new SubmitChecklistAction();

        $service = makeChecklistService($instanceRepo, $templateRepo, $action);

        expect(fn () => $service->submit($instance))
            ->toThrow(ValidationException::class);
    });

    it('marks instance as completed when all required answers are present', function () {
        $template = ChecklistTemplate::factory()->create();
        $question = ChecklistQuestion::factory()->create([
            'template_id' => $template->id,
            'required'    => true,
        ]);
        $auditor  = User::factory()->auditor()->create();
        $instance = ChecklistInstance::factory()->draft()->create([
            'template_id' => $template->id,
            'auditor_id'  => $auditor->id,
        ]);

        // Provide a non-empty answer for the required question
        ChecklistAnswer::factory()->create([
            'instance_id'  => $instance->id,
            'question_id'  => $question->id,
            'answer_value' => 'Yes',
        ]);

        $completedInstance = ChecklistInstance::factory()->completed()->make([
            'id'          => $instance->id,
            'template_id' => $template->id,
            'auditor_id'  => $auditor->id,
        ]);

        $instanceRepo = Mockery::mock(InstanceRepositoryInterface::class);
        $instanceRepo->shouldReceive('markCompleted')
            ->once()
            ->with(Mockery::type(ChecklistInstance::class))
            ->andReturn($completedInstance);

        $templateRepo = Mockery::mock(TemplateRepositoryInterface::class);
        $action       = new SubmitChecklistAction();

        $service = makeChecklistService($instanceRepo, $templateRepo, $action);

        $result = $service->submit($instance);

        expect($result->status)->toBe('completed');
    });

    it('throws AuthorizationException when instance is already completed', function () {
        $instance = ChecklistInstance::factory()->completed()->create();

        $instanceRepo = Mockery::mock(InstanceRepositoryInterface::class);
        $templateRepo = Mockery::mock(TemplateRepositoryInterface::class);
        $action       = new SubmitChecklistAction();

        $service = makeChecklistService($instanceRepo, $templateRepo, $action);

        expect(fn () => $service->submit($instance))
            ->toThrow(AuthorizationException::class);
    });
});

// ---------------------------------------------------------------------------
// saveDraft() — blocked on completed instance
// ---------------------------------------------------------------------------

describe('ChecklistService::saveDraft', function () {

    it('throws AuthorizationException when instance is already completed', function () {
        $instance = ChecklistInstance::factory()->completed()->create();

        $instanceRepo = Mockery::mock(InstanceRepositoryInterface::class);
        $templateRepo = Mockery::mock(TemplateRepositoryInterface::class);
        $action       = new SubmitChecklistAction();

        $service = makeChecklistService($instanceRepo, $templateRepo, $action);

        expect(fn () => $service->saveDraft($instance, []))
            ->toThrow(AuthorizationException::class);
    });

    it('calls upsertAnswers on draft instance', function () {
        $template = ChecklistTemplate::factory()->create();
        $auditor  = User::factory()->auditor()->create();
        $instance = ChecklistInstance::factory()->draft()->create([
            'template_id' => $template->id,
            'auditor_id'  => $auditor->id,
        ]);

        $answers = [
            ['question_id' => 1, 'answer_value' => 'Some answer'],
        ];

        $instanceRepo = Mockery::mock(InstanceRepositoryInterface::class);
        $instanceRepo->shouldReceive('upsertAnswers')
            ->once()
            ->with($instance, $answers);

        $templateRepo = Mockery::mock(TemplateRepositoryInterface::class);
        $action       = new SubmitChecklistAction();

        $service = makeChecklistService($instanceRepo, $templateRepo, $action);

        $service->saveDraft($instance, $answers);
    });
});
