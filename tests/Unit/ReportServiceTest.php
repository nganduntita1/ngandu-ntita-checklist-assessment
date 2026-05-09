<?php

use App\Models\ChecklistInstance;
use App\Models\ChecklistTemplate;
use App\Models\User;
use App\Repositories\Contracts\ReportRepositoryInterface;
use App\Services\ReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;

uses(RefreshDatabase::class);

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------

function makeReportService(ReportRepositoryInterface $repo): ReportService
{
    return new ReportService($repo);
}

// ---------------------------------------------------------------------------
// ReportService::list — filters are forwarded to the repository
// ---------------------------------------------------------------------------

describe('ReportService::list', function () {

    it('passes date_from filter to the repository', function () {
        $filters = ['date_from' => '2024-01-01'];

        $emptyPage = new LengthAwarePaginator([], 0, 15);

        $repo = Mockery::mock(ReportRepositoryInterface::class);
        $repo->shouldReceive('paginate')
            ->once()
            ->with(Mockery::on(fn ($f) => $f['date_from'] === '2024-01-01'))
            ->andReturn($emptyPage);

        $service = makeReportService($repo);
        $service->list($filters);
    });

    it('passes date_to filter to the repository', function () {
        $filters = ['date_to' => '2024-12-31'];

        $emptyPage = new LengthAwarePaginator([], 0, 15);

        $repo = Mockery::mock(ReportRepositoryInterface::class);
        $repo->shouldReceive('paginate')
            ->once()
            ->with(Mockery::on(fn ($f) => $f['date_to'] === '2024-12-31'))
            ->andReturn($emptyPage);

        $service = makeReportService($repo);
        $service->list($filters);
    });

    it('passes template_id filter to the repository', function () {
        $filters = ['template_id' => 5];

        $emptyPage = new LengthAwarePaginator([], 0, 15);

        $repo = Mockery::mock(ReportRepositoryInterface::class);
        $repo->shouldReceive('paginate')
            ->once()
            ->with(Mockery::on(fn ($f) => $f['template_id'] === 5))
            ->andReturn($emptyPage);

        $service = makeReportService($repo);
        $service->list($filters);
    });

    it('passes auditor_id filter to the repository', function () {
        $filters = ['auditor_id' => 7];

        $emptyPage = new LengthAwarePaginator([], 0, 15);

        $repo = Mockery::mock(ReportRepositoryInterface::class);
        $repo->shouldReceive('paginate')
            ->once()
            ->with(Mockery::on(fn ($f) => $f['auditor_id'] === 7))
            ->andReturn($emptyPage);

        $service = makeReportService($repo);
        $service->list($filters);
    });

    it('passes all filters together to the repository', function () {
        $filters = [
            'date_from'   => '2024-01-01',
            'date_to'     => '2024-12-31',
            'template_id' => 3,
            'auditor_id'  => 9,
        ];

        $emptyPage = new LengthAwarePaginator([], 0, 15);

        $repo = Mockery::mock(ReportRepositoryInterface::class);
        $repo->shouldReceive('paginate')
            ->once()
            ->with(Mockery::on(fn ($f) =>
                $f['date_from']   === '2024-01-01' &&
                $f['date_to']     === '2024-12-31' &&
                $f['template_id'] === 3 &&
                $f['auditor_id']  === 9
            ))
            ->andReturn($emptyPage);

        $service = makeReportService($repo);
        $service->list($filters);
    });

    it('results include auditor and template via eager loading', function () {
        // Use the real ReportRepository against the in-memory SQLite DB
        // to verify eager loading is applied (no N+1 queries).
        $admin    = User::factory()->admin()->create();
        $auditor  = User::factory()->auditor()->create();
        $template = ChecklistTemplate::factory()->create(['created_by' => $admin->id]);

        ChecklistInstance::factory()->count(3)->create([
            'template_id' => $template->id,
            'auditor_id'  => $auditor->id,
        ]);

        $repo    = new \App\Repositories\ReportRepository();
        $service = makeReportService($repo);

        $result = $service->list([]);

        expect($result->total())->toBe(3);

        // Each item should have auditor and template loaded (not null)
        foreach ($result->items() as $instance) {
            expect($instance->relationLoaded('auditor'))->toBeTrue();
            expect($instance->relationLoaded('template'))->toBeTrue();
            expect($instance->auditor)->not->toBeNull();
            expect($instance->template)->not->toBeNull();
        }
    });
});
