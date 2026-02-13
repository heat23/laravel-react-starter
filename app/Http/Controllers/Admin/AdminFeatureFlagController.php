<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminFeatureFlagRequest;
use App\Http\Requests\Admin\AdminFeatureFlagUserRequest;
use App\Services\AuditService;
use App\Services\FeatureFlagService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use InvalidArgumentException;
use RuntimeException;

class AdminFeatureFlagController extends Controller
{
    public function __construct(
        private FeatureFlagService $featureFlagService,
        private AuditService $auditService,
    ) {}

    /**
     * Display the feature flags index page.
     */
    public function index(): Response
    {
        return Inertia::render('Admin/FeatureFlags/Index', [
            'flags' => $this->featureFlagService->getAdminSummary(),
        ]);
    }

    /**
     * Update a global feature flag override.
     */
    public function updateGlobal(AdminFeatureFlagRequest $request, string $flag): JsonResponse
    {
        try {
            $enabled = $request->boolean('enabled');
            $reason = $request->input('reason');

            $this->featureFlagService->setGlobalOverride($flag, $enabled, $reason, $request->user());

            $this->auditService->log('admin.feature_flag.global_override', [
                'flag' => $flag,
                'enabled' => $enabled,
                'reason' => $reason,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Feature flag updated successfully.',
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Remove a global feature flag override.
     */
    public function removeGlobal(string $flag): JsonResponse
    {
        try {
            $this->featureFlagService->removeGlobalOverride($flag);

            $this->auditService->log('admin.feature_flag.global_override_removed', [
                'flag' => $flag,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Feature flag override removed.',
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get users with overrides for a flag.
     */
    public function getTargetedUsers(string $flag): JsonResponse
    {
        try {
            $users = $this->featureFlagService->getTargetedUsers($flag);

            return response()->json($users);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Add a user-specific override.
     */
    public function addUserOverride(AdminFeatureFlagUserRequest $request, string $flag): JsonResponse
    {
        try {
            $userId = $request->integer('user_id');
            $enabled = $request->boolean('enabled');
            $reason = $request->input('reason');

            $this->featureFlagService->setUserOverride($flag, $userId, $enabled, $reason, $request->user());

            $this->auditService->log('admin.feature_flag.user_override', [
                'flag' => $flag,
                'target_user_id' => $userId,
                'enabled' => $enabled,
                'reason' => $reason,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'User override added successfully.',
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Remove a user-specific override.
     */
    public function removeUserOverride(string $flag, int $user): JsonResponse
    {
        try {
            $this->featureFlagService->removeUserOverride($flag, $user);

            $this->auditService->log('admin.feature_flag.user_override_removed', [
                'flag' => $flag,
                'user_id' => $user,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'User override removed.',
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Remove all user overrides for a flag.
     */
    public function removeAllUserOverrides(string $flag): JsonResponse
    {
        try {
            $this->featureFlagService->removeAllUserOverrides($flag);

            $this->auditService->log('admin.feature_flag.all_user_overrides_removed', [
                'flag' => $flag,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'All user overrides removed.',
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Search users for targeting.
     */
    public function searchUsers(Request $request): JsonResponse
    {
        $query = $request->string('q')->trim()->toString();

        if (strlen($query) < 2) {
            return response()->json([
                'success' => false,
                'message' => 'Search query must be at least 2 characters.',
            ], 422);
        }

        $users = $this->featureFlagService->searchUsers($query);

        return response()->json($users);
    }
}
