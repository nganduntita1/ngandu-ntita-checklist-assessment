<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ChecklistInstance;
use App\Models\ChecklistTemplate;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Show the role-aware dashboard.
     *
     * GET /dashboard
     */
    public function index(): Response
    {
        $user = Auth::user();

        if ($user->role === 'admin') {
            $templateCount = ChecklistTemplate::count();

            $recentInstances = ChecklistInstance::with(['auditor', 'template'])
                ->latest()
                ->take(10)
                ->get()
                ->map(fn ($instance) => [
                    'id'             => $instance->id,
                    'auditor_name'   => $instance->auditor?->name,
                    'template_title' => $instance->template?->title,
                    'status'         => $instance->status,
                    'completed_at'   => $instance->completed_at?->toISOString(),
                ]);

            return Inertia::render('Dashboard', [
                'templateCount'   => $templateCount,
                'recentInstances' => $recentInstances,
            ]);
        }

        // Auditor view
        $draftCount = ChecklistInstance::where('auditor_id', $user->id)
            ->where('status', 'draft')
            ->count();

        $completedCount = ChecklistInstance::where('auditor_id', $user->id)
            ->where('status', 'completed')
            ->count();

        return Inertia::render('Dashboard', [
            'draftCount'     => $draftCount,
            'completedCount' => $completedCount,
        ]);
    }
}
