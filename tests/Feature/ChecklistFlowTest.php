<?php

use App\Models\ChecklistAnswer;
use App\Models\ChecklistInstance;
use App\Models\ChecklistQuestion;
use App\Models\ChecklistTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------

function makeAuditor(): User
{
    return User::factory()->auditor()->create();
}

function makeAdmin(): User
{
    return User::factory()->admin()->create();
}

/**
 * Create a template with a given number of required and optional questions.
 */
function makeTemplateWithQuestions(int $required = 2, int $optional = 1): ChecklistTemplate
{
    $admin    = makeAdmin();
    $template = ChecklistTemplate::factory()->active()->create(['created_by' => $admin->id]);

    ChecklistQuestion::factory()
        ->count($required)
        ->create([
            'template_id' => $template->id,
            'answer_type' => 'text',
            'required'    => true,
        ]);

    if ($optional > 0) {
        ChecklistQuestion::factory()
            ->count($optional)
            ->create([
                'template_id' => $template->id,
                'answer_type' => 'text',
                'required'    => false,
            ]);
    }

    return $template->fresh(['questions']);
}

// ---------------------------------------------------------------------------
// POST /api/checklists/start — Auditor can start a template
// ---------------------------------------------------------------------------

describe('POST /api/checklists/start', function () {

    it('auditor can start a template (201, instance created with status=draft, questions returned)', function () {
        $auditor  = makeAuditor();
        $template = makeTemplateWithQuestions(2, 1);

        $response = $this->actingAs($auditor, 'sanctum')
            ->postJson('/api/checklists/start', ['template_id' => $template->id]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Checklist started',
                'data'    => [
                    'status' => 'draft',
                ],
            ])
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'status',
                    'template' => [
                        'id',
                        'questions',
                    ],
                    'answers',
                ],
            ]);

        // Instance persisted in DB with correct auditor and status
        $instanceId = $response->json('data.id');

        $this->assertDatabaseHas('checklist_instances', [
            'id'          => $instanceId,
            'template_id' => $template->id,
            'auditor_id'  => $auditor->id,
            'status'      => 'draft',
        ]);

        // Questions are returned in the response
        $questions = $response->json('data.template.questions');
        expect($questions)->toHaveCount(3); // 2 required + 1 optional
    });

    it('admin cannot start a checklist (403)', function () {
        $admin    = makeAdmin();
        $template = makeTemplateWithQuestions(1, 0);

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/checklists/start', ['template_id' => $template->id]);

        $response->assertStatus(403);
    });

    it('returns 422 when template_id is missing', function () {
        $auditor = makeAuditor();

        $response = $this->actingAs($auditor, 'sanctum')
            ->postJson('/api/checklists/start', []);

        $response->assertStatus(422)
            ->assertJson(['success' => false]);
    });

    it('returns 422 when template_id does not exist', function () {
        $auditor = makeAuditor();

        $response = $this->actingAs($auditor, 'sanctum')
            ->postJson('/api/checklists/start', ['template_id' => 99999]);

        $response->assertStatus(422)
            ->assertJson(['success' => false]);
    });
});

// ---------------------------------------------------------------------------
// POST /api/checklists/{id}/save-draft — Save-draft persists answers
// ---------------------------------------------------------------------------

describe('POST /api/checklists/{id}/save-draft', function () {

    it('save-draft persists answers (200, answers in DB)', function () {
        $auditor  = makeAuditor();
        $template = makeTemplateWithQuestions(2, 0);
        $instance = ChecklistInstance::factory()->draft()->create([
            'template_id' => $template->id,
            'auditor_id'  => $auditor->id,
        ]);

        $questions = $template->questions;

        $answers = $questions->map(fn ($q) => [
            'question_id'  => $q->id,
            'answer_value' => 'Test answer for question ' . $q->id,
        ])->values()->all();

        $response = $this->actingAs($auditor, 'sanctum')
            ->postJson("/api/checklists/{$instance->id}/save-draft", [
                'answers' => $answers,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Draft saved',
                'data'    => [
                    'status' => 'draft',
                ],
            ]);

        // Each answer is persisted in the DB
        foreach ($answers as $answer) {
            $this->assertDatabaseHas('checklist_answers', [
                'instance_id'  => $instance->id,
                'question_id'  => $answer['question_id'],
                'answer_value' => $answer['answer_value'],
            ]);
        }
    });

    it('completed instance rejects save-draft with 403', function () {
        $auditor  = makeAuditor();
        $template = makeTemplateWithQuestions(1, 0);
        $instance = ChecklistInstance::factory()->completed()->create([
            'template_id' => $template->id,
            'auditor_id'  => $auditor->id,
        ]);

        $question = $template->questions->first();

        $response = $this->actingAs($auditor, 'sanctum')
            ->postJson("/api/checklists/{$instance->id}/save-draft", [
                'answers' => [
                    ['question_id' => $question->id, 'answer_value' => 'Some answer'],
                ],
            ]);

        $response->assertStatus(403);
    });

    it('auditor cannot save-draft on another auditor\'s instance (403)', function () {
        $owner    = makeAuditor();
        $intruder = makeAuditor();
        $template = makeTemplateWithQuestions(1, 0);
        $instance = ChecklistInstance::factory()->draft()->create([
            'template_id' => $template->id,
            'auditor_id'  => $owner->id,
        ]);

        $question = $template->questions->first();

        $response = $this->actingAs($intruder, 'sanctum')
            ->postJson("/api/checklists/{$instance->id}/save-draft", [
                'answers' => [
                    ['question_id' => $question->id, 'answer_value' => 'Intruder answer'],
                ],
            ]);

        $response->assertStatus(403);
    });
});

// ---------------------------------------------------------------------------
// POST /api/checklists/{id}/submit — Submit instance
// ---------------------------------------------------------------------------

describe('POST /api/checklists/{id}/submit', function () {

    it('submit with all required answers completes instance (200, status=completed, completed_at set)', function () {
        $auditor  = makeAuditor();
        $template = makeTemplateWithQuestions(2, 1);
        $instance = ChecklistInstance::factory()->draft()->create([
            'template_id' => $template->id,
            'auditor_id'  => $auditor->id,
        ]);

        // Answer all required questions
        $requiredQuestions = $template->questions->where('required', true);
        foreach ($requiredQuestions as $question) {
            ChecklistAnswer::factory()->create([
                'instance_id'  => $instance->id,
                'question_id'  => $question->id,
                'answer_value' => 'Compliant',
            ]);
        }

        $response = $this->actingAs($auditor, 'sanctum')
            ->postJson("/api/checklists/{$instance->id}/submit");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Checklist submitted',
                'data'    => [
                    'status' => 'completed',
                ],
            ]);

        // completed_at is set in the response
        expect($response->json('data.completed_at'))->not->toBeNull();

        // DB reflects completed status
        $this->assertDatabaseHas('checklist_instances', [
            'id'     => $instance->id,
            'status' => 'completed',
        ]);

        $instance->refresh();
        expect($instance->completed_at)->not->toBeNull();
    });

    it('submit with missing required answers returns 422 with unanswered list', function () {
        $auditor  = makeAuditor();
        $template = makeTemplateWithQuestions(2, 0); // 2 required, none answered
        $instance = ChecklistInstance::factory()->draft()->create([
            'template_id' => $template->id,
            'auditor_id'  => $auditor->id,
        ]);

        $response = $this->actingAs($auditor, 'sanctum')
            ->postJson("/api/checklists/{$instance->id}/submit");

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Validation failed',
            ]);

        // data.unanswered_questions must be present and contain the unanswered required questions
        $unanswered = $response->json('data.unanswered_questions');
        expect($unanswered)->not->toBeNull();
        expect($unanswered)->toHaveCount(2);

        // Each entry should have id and question_text
        foreach ($unanswered as $item) {
            expect($item)->toHaveKey('id');
            expect($item)->toHaveKey('question_text');
        }

        // Instance remains in draft status
        $this->assertDatabaseHas('checklist_instances', [
            'id'     => $instance->id,
            'status' => 'draft',
        ]);
    });

    it('submit with only some required answers returns 422 listing only unanswered questions', function () {
        $auditor  = makeAuditor();
        $template = makeTemplateWithQuestions(3, 0); // 3 required
        $instance = ChecklistInstance::factory()->draft()->create([
            'template_id' => $template->id,
            'auditor_id'  => $auditor->id,
        ]);

        // Answer only the first required question
        $firstQuestion = $template->questions->first();
        ChecklistAnswer::factory()->create([
            'instance_id'  => $instance->id,
            'question_id'  => $firstQuestion->id,
            'answer_value' => 'Answered',
        ]);

        $response = $this->actingAs($auditor, 'sanctum')
            ->postJson("/api/checklists/{$instance->id}/submit");

        $response->assertStatus(422);

        $unanswered = $response->json('data.unanswered_questions');
        expect($unanswered)->toHaveCount(2); // 2 of 3 still unanswered

        $unansweredIds = collect($unanswered)->pluck('id')->all();
        expect($unansweredIds)->not->toContain($firstQuestion->id);
    });

    it('auditor cannot submit another auditor\'s instance (403)', function () {
        $owner    = makeAuditor();
        $intruder = makeAuditor();
        $template = makeTemplateWithQuestions(1, 0);
        $instance = ChecklistInstance::factory()->draft()->create([
            'template_id' => $template->id,
            'auditor_id'  => $owner->id,
        ]);

        $response = $this->actingAs($intruder, 'sanctum')
            ->postJson("/api/checklists/{$instance->id}/submit");

        $response->assertStatus(403);
    });
});

// ---------------------------------------------------------------------------
// Cross-auditor access — GET /api/checklists (index only returns own instances)
// ---------------------------------------------------------------------------

describe('GET /api/checklists', function () {

    it('auditor only sees their own instances in the list', function () {
        $auditor1 = makeAuditor();
        $auditor2 = makeAuditor();
        $template = makeTemplateWithQuestions(1, 0);

        $ownInstance   = ChecklistInstance::factory()->draft()->create([
            'template_id' => $template->id,
            'auditor_id'  => $auditor1->id,
        ]);
        $otherInstance = ChecklistInstance::factory()->draft()->create([
            'template_id' => $template->id,
            'auditor_id'  => $auditor2->id,
        ]);

        $response = $this->actingAs($auditor1, 'sanctum')
            ->getJson('/api/checklists');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $ids = collect($response->json('data.data'))->pluck('id')->all();

        expect($ids)->toContain($ownInstance->id);
        expect($ids)->not->toContain($otherInstance->id);
    });

    it('admin cannot access the checklist list endpoint (403)', function () {
        $admin = makeAdmin();

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson('/api/checklists');

        $response->assertStatus(403);
    });
});
