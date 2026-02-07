<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UpdateSettingRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group User Settings
 *
 * Manage user preferences such as theme and timezone.
 */
class UserSettingsController extends Controller
{
    /**
     * Get settings
     *
     * Retrieve all settings for the authenticated user.
     *
     * @authenticated
     *
     * @response 200 {"theme":"system","timezone":"UTC"}
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'theme' => $user->getSetting('theme', 'system'),
            'timezone' => $user->getSetting('timezone', config('app.timezone')),
        ]);
    }

    /**
     * Update setting
     *
     * Create or update a user setting.
     *
     * @authenticated
     *
     * @response 200 {"success":true}
     */
    public function store(UpdateSettingRequest $request): JsonResponse
    {
        $user = $request->user();
        $user->setSetting($request->validated('key'), $request->validated('value'));

        return response()->json(['success' => true]);
    }
}
