<?php

namespace App\Http\Controllers;

use App\Enums\AnalyticsEvent;
use App\Http\Requests\FeedbackRequest;
use App\Models\Feedback;
use App\Notifications\FeedbackReceivedNotification;
use App\Services\AuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Notifications\AnonymousNotifiable;

class FeedbackController extends Controller
{
    public function __construct(private AuditService $auditService) {}

    public function store(FeedbackRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $feedback = Feedback::create([
            'user_id' => $request->user()?->id,
            'type' => $validated['type'],
            'message' => $validated['message'],
            'status' => 'open',
            'priority' => 'medium',
        ]);

        // Side-effect: audit log
        $this->auditService->log(AnalyticsEvent::FEEDBACK_SUBMITTED, [
            'type' => $validated['type'],
            'message' => $validated['message'],
            'feedback_id' => $feedback->id,
        ]);

        // Notify the operator if configured
        $notifyEmail = config('feedback.notify_email');
        if ($notifyEmail) {
            $feedback->load('user');
            (new AnonymousNotifiable)
                ->route('mail', $notifyEmail)
                ->notify(new FeedbackReceivedNotification($feedback));
        }

        return response()->json(['success' => true, 'message' => 'Thank you for your feedback!']);
    }
}
