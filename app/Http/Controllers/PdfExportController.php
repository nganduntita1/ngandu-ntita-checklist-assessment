<?php

namespace App\Http\Controllers;

use App\Jobs\GeneratePdfJob;
use App\Models\ChecklistInstance;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PdfExportController extends Controller
{
    /**
     * Dispatch the PDF generation job for a completed checklist instance.
     *
     * POST /api/checklists/{checklist}/export-pdf
     */
    public function export(Request $request, ChecklistInstance $checklist): JsonResponse
    {
        $this->authorize('view', $checklist);

        GeneratePdfJob::dispatch($checklist, $request->user()->id);

        return response()->json([
            'success' => true,
            'message' => 'PDF generation queued',
            'data'    => null,
        ], 202);
    }

    /**
     * Stream the generated PDF file to the client.
     *
     * GET /api/checklists/{checklist}/download-pdf
     */
    public function download(Request $request, ChecklistInstance $checklist): StreamedResponse|JsonResponse
    {
        $this->authorize('view', $checklist);

        $path = "exports/checklist-{$checklist->id}.pdf";

        if (! Storage::exists($path)) {
            return response()->json([
                'success' => false,
                'message' => 'PDF not yet generated. Please request an export first.',
                'data'    => null,
            ], 404);
        }

        return Storage::download($path, "checklist-{$checklist->id}.pdf", [
            'Content-Type' => 'application/pdf',
        ]);
    }
}
