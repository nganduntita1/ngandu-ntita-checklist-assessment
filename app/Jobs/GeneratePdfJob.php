<?php

namespace App\Jobs;

use App\Models\ChecklistInstance;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class GeneratePdfJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public int $backoff = 60;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public readonly ChecklistInstance $instance,
        public readonly int $requestingUserId
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Eager-load all required relations
        $this->instance->load(['template.questions', 'auditor', 'answers.question']);

        // Generate the PDF from the Blade view
        $pdf = Pdf::loadView('pdf.checklist', ['instance' => $this->instance]);

        // Store the PDF in the exports directory
        $path = "exports/checklist-{$this->instance->id}.pdf";
        Storage::put($path, $pdf->output());

        // Persist the pdf_path on the instance
        $this->instance->update(['pdf_path' => $path]);
    }

    /**
     * Handle a job failure.
     */
    public function failed(Throwable $exception): void
    {
        Log::error('PDF generation failed', [
            'instance_id' => $this->instance->id,
            'error'       => $exception->getMessage(),
        ]);
    }
}
