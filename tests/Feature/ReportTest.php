<?php

use App\Models\ChecklistInstance;
use App\Models\ChecklistTemplate;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------

function reportAdmin(): User
{
    return User::factory()->admin()->create();
}

function reportAuditor(): User
{
    return User::factory()->auditor()->create();
}

// ---------------------------------------------------------------------------
// GET /api/reports — Admin can list reports
// ---------------------------------------------------------------------------

describe('GET /api/reports', function () {

    it('admin can list reports (200, success: true, paginated data with expected fields)', function () {
        $admin   = reportAdmin();
        $auditor = reportAuditor();
        $template = ChecklistTemplate::factory()->active()->create();

        ChecklistInstance::factory()->completed()->create([
            'template_id' => $template->id,
            'auditor_id'  => $auditor->id,
        ]);

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson('/api/reports');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Report retrieved',
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'data' => [
                        '*' => [
                            'id',
                            'auditor' => ['name'],
                            'template' => ['title'],
                            'status',
                            'completed_at',
                            'created_at',
                        ],
                    ],
                    'meta' => [
                        'current_page',
                        'last_page',
                        'per_page',
                        'total',
                    ],
                ],
            ]);

        $items = $response->json('data.data');
        expect($items)->toHaveCount(1);

        $item = $items[0];
        expect($item['auditor']['name'])->toBe($auditor->name);
        expect($item['template']['title'])->toBe($template->title);
        expect($item['status'])->toBe('completed');
        expect($item['completed_at'])->not->toBeNull();
    });

    it('auditor cannot access reports (403)', function () {
        $auditor = reportAuditor();

        $response = $this->actingAs($auditor, 'sanctum')
            ->getJson('/api/reports');

        $response->assertStatus(403);
    });
});

// ---------------------------------------------------------------------------
// GET /api/reports — date_from filter
// ---------------------------------------------------------------------------

describe('GET /api/reports — date_from filter', function () {

    it('date_from filter includes only instances created on or after that date', function () {
        $admin    = reportAdmin();
        $auditor  = reportAuditor();
        $template = ChecklistTemplate::factory()->active()->create();

        $before = ChecklistInstance::factory()->create([
            'template_id' => $template->id,
            'auditor_id'  => $auditor->id,
            'created_at'  => Carbon::parse('2024-01-10'),
        ]);

        $onDate = ChecklistInstance::factory()->create([
            'template_id' => $template->id,
            'auditor_id'  => $auditor->id,
            'created_at'  => Carbon::parse('2024-01-15'),
        ]);

        $after = ChecklistInstance::factory()->create([
            'template_id' => $template->id,
            'auditor_id'  => $auditor->id,
            'created_at'  => Carbon::parse('2024-01-20'),
        ]);

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson('/api/reports?date_from=2024-01-15');

        $response->assertStatus(200);

        $ids = collect($response->json('data.data'))->pluck('id')->all();

        expect($ids)->toContain($onDate->id);
        expect($ids)->toContain($after->id);
        expect($ids)->not->toContain($before->id);
    });
});

// ---------------------------------------------------------------------------
// GET /api/reports — date_to filter
// ---------------------------------------------------------------------------

describe('GET /api/reports — date_to filter', function () {

    it('date_to filter includes only instances created on or before that date', function () {
        $admin    = reportAdmin();
        $auditor  = reportAuditor();
        $template = ChecklistTemplate::factory()->active()->create();

        $before = ChecklistInstance::factory()->create([
            'template_id' => $template->id,
            'auditor_id'  => $auditor->id,
            'created_at'  => Carbon::parse('2024-01-10'),
        ]);

        $onDate = ChecklistInstance::factory()->create([
            'template_id' => $template->id,
            'auditor_id'  => $auditor->id,
            'created_at'  => Carbon::parse('2024-01-15'),
        ]);

        $after = ChecklistInstance::factory()->create([
            'template_id' => $template->id,
            'auditor_id'  => $auditor->id,
            'created_at'  => Carbon::parse('2024-01-20'),
        ]);

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson('/api/reports?date_to=2024-01-15');

        $response->assertStatus(200);

        $ids = collect($response->json('data.data'))->pluck('id')->all();

        expect($ids)->toContain($before->id);
        expect($ids)->toContain($onDate->id);
        expect($ids)->not->toContain($after->id);
    });
});

// ---------------------------------------------------------------------------
// GET /api/reports — date_from + date_to combined filter
// ---------------------------------------------------------------------------

describe('GET /api/reports — date_from + date_to combined filter', function () {

    it('date_from and date_to together filter to the correct range', function () {
        $admin    = reportAdmin();
        $auditor  = reportAuditor();
        $template = ChecklistTemplate::factory()->active()->create();

        $tooEarly = ChecklistInstance::factory()->create([
            'template_id' => $template->id,
            'auditor_id'  => $auditor->id,
            'created_at'  => Carbon::parse('2024-01-05'),
        ]);

        $inRange1 = ChecklistInstance::factory()->create([
            'template_id' => $template->id,
            'auditor_id'  => $auditor->id,
            'created_at'  => Carbon::parse('2024-01-10'),
        ]);

        $inRange2 = ChecklistInstance::factory()->create([
            'template_id' => $template->id,
            'auditor_id'  => $auditor->id,
            'created_at'  => Carbon::parse('2024-01-20'),
        ]);

        $tooLate = ChecklistInstance::factory()->create([
            'template_id' => $template->id,
            'auditor_id'  => $auditor->id,
            'created_at'  => Carbon::parse('2024-01-25'),
        ]);

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson('/api/reports?date_from=2024-01-10&date_to=2024-01-20');

        $response->assertStatus(200);

        $ids = collect($response->json('data.data'))->pluck('id')->all();

        expect($ids)->toContain($inRange1->id);
        expect($ids)->toContain($inRange2->id);
        expect($ids)->not->toContain($tooEarly->id);
        expect($ids)->not->toContain($tooLate->id);
    });
});

// ---------------------------------------------------------------------------
// GET /api/reports — template_id filter
// ---------------------------------------------------------------------------

describe('GET /api/reports — template_id filter', function () {

    it('template_id filter returns only instances for that template', function () {
        $admin     = reportAdmin();
        $auditor   = reportAuditor();
        $template1 = ChecklistTemplate::factory()->active()->create();
        $template2 = ChecklistTemplate::factory()->active()->create();

        $instance1 = ChecklistInstance::factory()->create([
            'template_id' => $template1->id,
            'auditor_id'  => $auditor->id,
        ]);

        $instance2 = ChecklistInstance::factory()->create([
            'template_id' => $template2->id,
            'auditor_id'  => $auditor->id,
        ]);

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson("/api/reports?template_id={$template1->id}");

        $response->assertStatus(200);

        $ids = collect($response->json('data.data'))->pluck('id')->all();

        expect($ids)->toContain($instance1->id);
        expect($ids)->not->toContain($instance2->id);
    });
});

// ---------------------------------------------------------------------------
// GET /api/reports — auditor_id filter
// ---------------------------------------------------------------------------

describe('GET /api/reports — auditor_id filter', function () {

    it('auditor_id filter returns only instances for that auditor', function () {
        $admin    = reportAdmin();
        $auditor1 = reportAuditor();
        $auditor2 = reportAuditor();
        $template = ChecklistTemplate::factory()->active()->create();

        $instance1 = ChecklistInstance::factory()->create([
            'template_id' => $template->id,
            'auditor_id'  => $auditor1->id,
        ]);

        $instance2 = ChecklistInstance::factory()->create([
            'template_id' => $template->id,
            'auditor_id'  => $auditor2->id,
        ]);

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson("/api/reports?auditor_id={$auditor1->id}");

        $response->assertStatus(200);

        $ids = collect($response->json('data.data'))->pluck('id')->all();

        expect($ids)->toContain($instance1->id);
        expect($ids)->not->toContain($instance2->id);
    });
});
