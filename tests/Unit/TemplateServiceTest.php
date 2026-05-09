<?php

use App\Models\ChecklistTemplate;
use App\Models\User;
use App\Repositories\Contracts\TemplateRepositoryInterface;
use App\Services\TemplateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------

function makeTemplateService(TemplateRepositoryInterface $repo): TemplateService
{
    return new TemplateService($repo);
}

// ---------------------------------------------------------------------------
// create() — wraps in DB transaction
// ---------------------------------------------------------------------------

describe('TemplateService::create', function () {

    it('wraps template and question creation in a DB transaction', function () {
        $transactionCalled = false;

        // Spy on DB::transaction by wrapping it
        DB::shouldReceive('transaction')
            ->once()
            ->andReturnUsing(function (callable $callback) use (&$transactionCalled) {
                $transactionCalled = true;
                return $callback();
            });

        $template = new ChecklistTemplate(['id' => 1, 'title' => 'Test', 'status' => 'active']);
        $template->exists = true;

        $repo = Mockery::mock(TemplateRepositoryInterface::class);
        $repo->shouldReceive('create')->once()->andReturn($template);
        $repo->shouldReceive('syncQuestions')->once();

        $admin = new User();
        $admin->forceFill(['id' => 1, 'role' => 'admin']);

        $service = makeTemplateService($repo);

        $service->create([
            'title'     => 'Test',
            'status'    => 'active',
            'questions' => [
                ['question_text' => 'Q1?', 'answer_type' => 'text', 'required' => true, 'sort_order' => 1],
            ],
        ], $admin);

        expect($transactionCalled)->toBeTrue();
    });

    it('calls repository create with correct data', function () {
        DB::shouldReceive('transaction')
            ->once()
            ->andReturnUsing(fn (callable $cb) => $cb());

        $template = new ChecklistTemplate(['id' => 1, 'title' => 'My Template', 'status' => 'active']);
        $template->exists = true;

        $admin = new User();
        $admin->forceFill(['id' => 42, 'role' => 'admin']);

        $repo = Mockery::mock(TemplateRepositoryInterface::class);
        $repo->shouldReceive('create')
            ->once()
            ->with(Mockery::on(fn ($data) =>
                $data['title'] === 'My Template' &&
                $data['created_by'] === 42
            ))
            ->andReturn($template);
        $repo->shouldReceive('syncQuestions')->once();

        $service = makeTemplateService($repo);

        $service->create([
            'title'     => 'My Template',
            'questions' => [['question_text' => 'Q?', 'answer_type' => 'text', 'required' => true, 'sort_order' => 0]],
        ], $admin);
    });

    it('does not call syncQuestions when no questions are provided', function () {
        DB::shouldReceive('transaction')
            ->once()
            ->andReturnUsing(fn (callable $cb) => $cb());

        $template = new ChecklistTemplate(['id' => 1, 'title' => 'No Questions', 'status' => 'active']);
        $template->exists = true;

        $admin = new User();
        $admin->forceFill(['id' => 1, 'role' => 'admin']);

        $repo = Mockery::mock(TemplateRepositoryInterface::class);
        $repo->shouldReceive('create')->once()->andReturn($template);
        $repo->shouldNotReceive('syncQuestions');

        $service = makeTemplateService($repo);

        $service->create(['title' => 'No Questions'], $admin);
    });
});

// ---------------------------------------------------------------------------
// update() — syncs questions via repository
// ---------------------------------------------------------------------------

describe('TemplateService::update', function () {

    it('syncs questions when questions key is present in data', function () {
        DB::shouldReceive('transaction')
            ->once()
            ->andReturnUsing(fn (callable $cb) => $cb());

        $template = new ChecklistTemplate(['id' => 1, 'title' => 'Original', 'status' => 'active']);
        $template->exists = true;

        $updatedTemplate = new ChecklistTemplate(['id' => 1, 'title' => 'Updated', 'status' => 'active']);
        $updatedTemplate->exists = true;

        $questions = [
            ['question_text' => 'New Q?', 'answer_type' => 'boolean', 'required' => true, 'sort_order' => 1],
        ];

        $repo = Mockery::mock(TemplateRepositoryInterface::class);
        $repo->shouldReceive('update')->once()->andReturn($updatedTemplate);
        $repo->shouldReceive('syncQuestions')
            ->once()
            ->with($updatedTemplate, $questions);

        $service = makeTemplateService($repo);

        $service->update($template, ['title' => 'Updated', 'questions' => $questions]);
    });

    it('does not call syncQuestions when questions key is absent', function () {
        DB::shouldReceive('transaction')
            ->once()
            ->andReturnUsing(fn (callable $cb) => $cb());

        $template = new ChecklistTemplate(['id' => 1, 'title' => 'Original', 'status' => 'active']);
        $template->exists = true;

        $updatedTemplate = new ChecklistTemplate(['id' => 1, 'title' => 'Updated', 'status' => 'active']);
        $updatedTemplate->exists = true;

        $repo = Mockery::mock(TemplateRepositoryInterface::class);
        $repo->shouldReceive('update')->once()->andReturn($updatedTemplate);
        $repo->shouldNotReceive('syncQuestions');

        $service = makeTemplateService($repo);

        $service->update($template, ['title' => 'Updated']);
    });

    it('wraps update in a DB transaction', function () {
        $transactionCalled = false;

        DB::shouldReceive('transaction')
            ->once()
            ->andReturnUsing(function (callable $cb) use (&$transactionCalled) {
                $transactionCalled = true;
                return $cb();
            });

        $template = new ChecklistTemplate(['id' => 1, 'title' => 'T', 'status' => 'active']);
        $template->exists = true;

        $updatedTemplate = new ChecklistTemplate(['id' => 1, 'title' => 'T2', 'status' => 'active']);
        $updatedTemplate->exists = true;

        $repo = Mockery::mock(TemplateRepositoryInterface::class);
        $repo->shouldReceive('update')->once()->andReturn($updatedTemplate);

        $service = makeTemplateService($repo);
        $service->update($template, ['title' => 'T2']);

        expect($transactionCalled)->toBeTrue();
    });
});

// ---------------------------------------------------------------------------
// delete() — removes template via repository
// ---------------------------------------------------------------------------

describe('TemplateService::delete', function () {

    it('delegates deletion to the repository', function () {
        $template = new ChecklistTemplate(['id' => 1, 'title' => 'To Delete', 'status' => 'active']);
        $template->exists = true;

        $repo = Mockery::mock(TemplateRepositoryInterface::class);
        $repo->shouldReceive('delete')
            ->once()
            ->with($template);

        $service = makeTemplateService($repo);
        $service->delete($template);
    });
});
