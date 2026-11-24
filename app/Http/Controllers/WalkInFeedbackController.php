<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWalkInFeedbackRequest;
use App\Models\WalkInFeedback;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class WalkInFeedbackController extends Controller
{
    public function create(): View
    {
        return view('walk-in.feedback', [
            'defaultVisitedAt' => now()->format('Y-m-d\TH:i'),
            'satisfactionOptions' => WalkInFeedback::SATISFACTION_LABELS,
            'satisfactionEmojis' => WalkInFeedback::SATISFACTION_EMOJIS,
        ]);
    }

    public function store(StoreWalkInFeedbackRequest $request): RedirectResponse
    {
        WalkInFeedback::create([
            'client_name' => $request->filled('client_name') ? $request->string('client_name')->trim() : null,
            'client_email' => $request->filled('client_email') ? $request->string('client_email')->trim() : null,
            'visited_at' => $request->date('visited_at'),
            'recommend_score' => $request->integer('recommend_score'),
            'rating_reason' => $request->string('rating_reason')->trim(),
            'satisfaction_level' => $request->integer('satisfaction_level'),
            'improvement_suggestions' => $request->filled('improvement_suggestions')
                ? $request->string('improvement_suggestions')->trim()
                : null,
            'ip_address' => $request->ip(),
        ]);

        return redirect()
            ->route('walk-in.feedback.create')
            ->with('feedback_submitted', true);
    }
}