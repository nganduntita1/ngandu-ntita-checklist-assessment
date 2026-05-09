<?php

use App\Models\ChecklistQuestion;
use App\Models\ChecklistTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------

function adminUser(): User
{
    return User::factory()->admin()->create();
}

function auditorUser(): User
{
    return User::factory()->auditor()->create();
}

function validTemplatePayload(array $overrides = []): array
{
    return array_merge([
        'title'       => 'Test Compliance Template',
        'description' => 'A template used in tests.',
        'status'      => 'active',
        'questions'   => [
            [
                'question_text' => 'Is the fire exit clearly marked?',
                'answer_type'   => 'boolean',
                'required'      => true,
                'sort_order'    => 1,
            ],
            [
                'question_text' => 'Describe the safety procedures.',
                'answer_type'   => 'textarea',
                'required'      => false,
                'sort_order'    => 2,
            ],
        ],
    ], $overrides);
}

// ---------------------------------------------------------------------------
// POST /api/templates — Admin can create a template with questions
// ---------------------------------------------------------------------------

describe('POST /api/templates', function () {

    it('admin can create a template with questions (201, data persisted)', function () {
        $admin = adminUser();

        $payload = validTemplatePayload();

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/templates', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Template created',
            ])
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'title',
                    'description',
                    'status',
                    'questions',
                ],
            ]);

        // Template persisted in DB
        $this->assertDatabaseHas('checklist_templates', [
            'title'  => 'Test Compliance Template',
            'status' => 'active',
        ]);

        // Both questions persisted in DB
        $templateId = $response->json('data.id');

        $this->assertDatabaseHas('checklist_questions', [
            'template_id'   => $templateId,
            'question_text' => 'Is the fire exit clearly marked?',
            'answer_type'   => 'boolean',
        ]);

        $this->assertDatabaseHas('checklist_questions', [
            'template_id'   => $templateId,
            'question_text' => 'Describe the safety procedures.',
            'answer_type'   => 'textarea',
        ]);
    });

    it('auditor cannot create a template (403)', function () {
        $auditor = auditorUser();

        $response = $this->actingAs($auditor, 'sanctum')
            ->postJson('/api/templates', validTemplatePayload());

        $response->assertStatus(403);
    });

    it('returns 422 when title is missing', function () {
        $admin = adminUser();

        $payload = validTemplatePayload(['title' => '']);

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/templates', $payload);

        $response->assertStatus(422)
            ->assertJson(['success' => false, 'message' => 'Validation failed']);

        expect($response->json('data'))->toHaveKey('title');
    });

    it('returns 422 when questions array is missing', function () {
        $admin = adminUser();

        $payload = validTemplatePayload();
        unset($payload['questions']);

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/templates', $payload);

        $response->assertStatus(422)
            ->assertJson(['success' => false, 'message' => 'Validation failed']);

        expect($response->json('data'))->toHaveKey('questions');
    });

    it('returns 422 when a question has an invalid answer_type', function () {
        $admin = adminUser();

        $payload = validTemplatePayload([
            'questions' => [
                [
                    'question_text' => 'Some question?',
                    'answer_type'   => 'invalid_type',
                    'required'      => true,
                    'sort_order'    => 1,
                ],
            ],
        ]);

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/templates', $payload);

        $response->assertStatus(422)
            ->assertJson(['success' => false, 'message' => 'Validation failed']);

        // Errors are returned as flat keys with dots, e.g. "questions.0.answer_type"
        expect($response->json('data'))->toHaveKey('questions.0.answer_type');
    });

    it('returns 422 when questions array is empty', function () {
        $admin = adminUser();

        $payload = validTemplatePayload(['questions' => []]);

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/templates', $payload);

        $response->assertStatus(422)
            ->assertJson(['success' => false, 'message' => 'Validation failed']);

        expect($response->json('data'))->toHaveKey('questions');
    });

    it('returns 422 when a question is missing question_text', function () {
        $admin = adminUser();

        $payload = validTemplatePayload([
            'questions' => [
                [
                    'answer_type' => 'text',
                    'required'    => true,
                ],
            ],
        ]);

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/templates', $payload);

        $response->assertStatus(422)
            ->assertJson(['success' => false, 'message' => 'Validation failed']);

        // Errors are returned as flat keys with dots, e.g. "questions.0.question_text"
        expect($response->json('data'))->toHaveKey('questions.0.question_text');
    });
});

// ---------------------------------------------------------------------------
// PUT /api/templates/{id} — Admin can update a template
// ---------------------------------------------------------------------------

describe('PUT /api/templates/{id}', function () {

    it('admin can update a template (200, updated values reflected)', function () {
        $admin    = adminUser();
        $template = ChecklistTemplate::factory()->create(['created_by' => $admin->id]);

        $response = $this->actingAs($admin, 'sanctum')
            ->putJson("/api/templates/{$template->id}", [
                'title'  => 'Updated Title',
                'status' => 'inactive',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Template updated',
                'data'    => [
                    'title'  => 'Updated Title',
                    'status' => 'inactive',
                ],
            ]);

        $this->assertDatabaseHas('checklist_templates', [
            'id'     => $template->id,
            'title'  => 'Updated Title',
            'status' => 'inactive',
        ]);
    });

    it('auditor cannot update a template (403)', function () {
        $admin    = adminUser();
        $auditor  = auditorUser();
        $template = ChecklistTemplate::factory()->create(['created_by' => $admin->id]);

        $response = $this->actingAs($auditor, 'sanctum')
            ->putJson("/api/templates/{$template->id}", ['title' => 'Hacked']);

        $response->assertStatus(403);
    });
});

// ---------------------------------------------------------------------------
// DELETE /api/templates/{id} — Admin can delete a template; questions cascade
// ---------------------------------------------------------------------------

describe('DELETE /api/templates/{id}', function () {

    it('admin can delete a template and questions cascade-delete (200)', function () {
        $admin    = adminUser();
        $template = ChecklistTemplate::factory()
            ->has(ChecklistQuestion::factory()->count(3), 'questions')
            ->create(['created_by' => $admin->id]);

        $templateId = $template->id;

        // Confirm questions exist before deletion
        expect(ChecklistQuestion::where('template_id', $templateId)->count())->toBe(3);

        $response = $this->actingAs($admin, 'sanctum')
            ->deleteJson("/api/templates/{$templateId}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Template deleted',
            ]);

        // Template removed from DB
        $this->assertDatabaseMissing('checklist_templates', ['id' => $templateId]);

        // All questions cascade-deleted
        expect(ChecklistQuestion::where('template_id', $templateId)->count())->toBe(0);
    });

    it('auditor cannot delete a template (403)', function () {
        $admin    = adminUser();
        $auditor  = auditorUser();
        $template = ChecklistTemplate::factory()->create(['created_by' => $admin->id]);

        $response = $this->actingAs($auditor, 'sanctum')
            ->deleteJson("/api/templates/{$template->id}");

        $response->assertStatus(403);
    });
});

// ---------------------------------------------------------------------------
// GET /api/templates — Search and pagination
// ---------------------------------------------------------------------------

describe('GET /api/templates', function () {

    it('search filters by title (only matching templates returned)', function () {
        $admin = adminUser();

        ChecklistTemplate::factory()->create([
            'title'      => 'Fire Safety Inspection 2024',
            'created_by' => $admin->id,
        ]);

        ChecklistTemplate::factory()->create([
            'title'      => 'GDPR Readiness Assessment 2024',
            'created_by' => $admin->id,
        ]);

        ChecklistTemplate::factory()->create([
            'title'      => 'HR Policy Compliance 2024',
            'created_by' => $admin->id,
        ]);

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson('/api/templates?search=Fire');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $titles = collect($response->json('data.data'))->pluck('title');

        expect($titles)->toContain('Fire Safety Inspection 2024');
        expect($titles)->not->toContain('GDPR Readiness Assessment 2024');
        expect($titles)->not->toContain('HR Policy Compliance 2024');
    });

    it('pagination returns correct page size (15 per page, meta reflects total)', function () {
        $admin = adminUser();

        // Create 20 templates directly to avoid the factory's unique() title pool overflow
        for ($i = 1; $i <= 20; $i++) {
            ChecklistTemplate::create([
                'title'      => "Pagination Test Template {$i}",
                'description' => null,
                'status'     => 'active',
                'created_by' => $admin->id,
            ]);
        }

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson('/api/templates?page=1');

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonStructure([
                'data' => [
                    'data',
                    'meta' => [
                        'current_page',
                        'last_page',
                        'per_page',
                        'total',
                    ],
                ],
            ]);

        $meta = $response->json('data.meta');

        expect(count($response->json('data.data')))->toBe(15);
        expect($meta['per_page'])->toBe(15);
        expect($meta['total'])->toBe(20);
        expect($meta['last_page'])->toBe(2);
        expect($meta['current_page'])->toBe(1);
    });
});
