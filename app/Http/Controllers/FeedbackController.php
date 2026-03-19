<?php

namespace App\Http\Controllers;

use App\Enums\AnalyticsEvent;
use App\Http\Requests\FeedbackRequest;
use App\Services\AuditService;
use Illuminate\Http\JsonResponse;

class FeedbackController extends Controller
{
    public function __construct(private AuditService $auditService) {}

    public function store(FeedbackRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $this->auditService->log(AnalyticsEvent::FEEDBACK_SUBMITTED, [
            'type' => $validated['type'],
            'message' => $validated['message'],
        ]);

        return response()->json(['success' => true, 'message' => 'Thank you for your feedback!']);
    }
}
