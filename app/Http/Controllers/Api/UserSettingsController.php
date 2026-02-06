<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UpdateSettingRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserSettingsController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'theme' => $user->getSetting('theme', 'system'),
            'timezone' => $user->getSetting('timezone', config('app.timezone')),
        ]);
    }

    public function store(UpdateSettingRequest $request): JsonResponse
    {
        $user = $request->user();
        $user->setSetting($request->validated('key'), $request->validated('value'));

        return response()->json(['success' => true]);
    }
}
