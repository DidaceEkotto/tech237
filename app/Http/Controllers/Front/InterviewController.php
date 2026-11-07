<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use App\Models\Interview;

class InterviewController extends Controller
{
    public function index()
    {
        $interviews = Interview::with('author')
            ->where('status', 'published')
            ->latest()
            ->get();

        return Inertia::render('Interviews/Index', [
            'interviews' => $interviews,
        ]);
    }

    public function show(string $slug)
    {
        $interview = Interview::with('author')
            ->where('slug', $slug)
            ->firstOrFail();

        $interview->increment('views_count');

        return Inertia::render('Interviews/Show', [
            'interview' => $interview,
        ]);
    }
}
