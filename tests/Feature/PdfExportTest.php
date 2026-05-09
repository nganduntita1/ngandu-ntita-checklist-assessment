<?php

use App\Jobs\GeneratePdfJob;
use App\Models\ChecklistInstance;
use App\Models\ChecklistTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------

function pdfAuditor(): User
{
    return User::factory()->auditor()->create();
}

function pdfAdmin(): User
{
    return User::factory()->admin()->create();
}

function pdfInstance(User $auditor, array $attributes = []): ChecklistInstance
{
    $template = ChecklistTemplate::factory()->active()->create();

    return ChecklistInstance::factory()->create(array_merge([
        'template_id' => $template->id,
        'auditor_id'  => $auditor->id,
    ], $attributes));
}

// ---------------------------------------------------------------------------
// POST /api/checklists/{id}/export-pdf — dispatches GeneratePdfJob
// ---------------------------------------------------------------------------

describe('POST /api/checklists/{id}/export-pdf', function () {

    it('dispatches GeneratePdfJob and returns 202 for the owning auditor', function () {
        Queue::fake();

        $auditor  = pdfAuditor();
        $instance = pdfInstance($auditor);

        $response = $this->actingAs($auditor, 'sanctum')
            ->postJson("/api/checklists/{$instance->id}/export-pdf");

        $response->assertStatus(202)
            ->assertJson([
                'success' => true,
                'message' => 'PDF generation queued',
                'data'    => null,
            ]);

        Queue::assertPushed(GeneratePdfJob::class, function (GeneratePdfJob $job) use ($instance, $auditor) {
            return $job->instance->id === $instance->id
                && $job->requestingUserId === $auditor->id;
        });
    });

    it('returns 403 when a user who does not own the checklist tries to export', function () {
        Queue::fake();

        $auditor  = pdfAuditor();
        $admin    = pdfAdmin();
        $instance = pdfInstance($auditor);

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/checklists/{$instance->id}/export-pdf");

        $response->assertStatus(403);

        Queue::assertNothingPushed();
    });

    it('returns 401 for unauthenticated export requests', function () {
        Queue::fake();

        $auditor  = pdfAuditor();
        $instance = pdfInstance($auditor);

        $response = $this->postJson("/api/checklists/{$instance->id}/export-pdf");

        $response->assertStatus(401);

        Queue::assertNothingPushed();
    });
});

// ---------------------------------------------------------------------------
// GET /api/checklists/{id}/download-pdf — streams PDF or returns 404
// ---------------------------------------------------------------------------

describe('GET /api/checklists/{id}/download-pdf', function () {

    it('returns the PDF file (200) when the PDF has been generated', function () {
        Storage::fake();

        $auditor  = pdfAuditor();
        $instance = pdfInstance($auditor);

        $path = "exports/checklist-{$instance->id}.pdf";
        Storage::put($path, '%PDF-1.4 fake pdf content');

        $instance->update(['pdf_path' => $path]);

        $response = $this->actingAs($auditor, 'sanctum')
            ->get("/api/checklists/{$instance->id}/download-pdf");

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    });

    it('returns 404 when pdf_path is null (PDF not yet generated)', function () {
        Storage::fake();

        $auditor  = pdfAuditor();
        $instance = pdfInstance($auditor, ['pdf_path' => null]);

        $response = $this->actingAs($auditor, 'sanctum')
            ->getJson("/api/checklists/{$instance->id}/download-pdf");

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'PDF not yet generated. Please request an export first.',
                'data'    => null,
            ]);
    });

    it('returns 403 when a user who does not own the checklist tries to download', function () {
        Storage::fake();

        $auditor  = pdfAuditor();
        $admin    = pdfAdmin();
        $instance = pdfInstance($auditor);

        $path = "exports/checklist-{$instance->id}.pdf";
        Storage::put($path, '%PDF-1.4 fake pdf content');
        $instance->update(['pdf_path' => $path]);

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson("/api/checklists/{$instance->id}/download-pdf");

        $response->assertStatus(403);
    });

    it('returns 401 for unauthenticated download requests', function () {
        Storage::fake();

        $auditor  = pdfAuditor();
        $instance = pdfInstance($auditor);

        $response = $this->getJson("/api/checklists/{$instance->id}/download-pdf");

        $response->assertStatus(401);
    });
});
