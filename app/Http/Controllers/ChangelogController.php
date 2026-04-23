<?php

namespace App\Http\Controllers;

use App\Models\UserSetting;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class ChangelogController extends Controller
{
    public function index(): Response
    {
        $entries = [];
        $path = public_path('changelog.json');

        if (file_exists($path)) {
            $entries = json_decode(file_get_contents($path), true) ?? [];
        }

        return Inertia::render('Public/Changelog', [
            'entries' => $entries,
        ]);
    }

    /**
     * Mark the latest changelog version as seen for the authenticated user.
     */
    public function acknowledge(): JsonResponse
    {
        $latestVersion = $this->latestVersion();

        if ($latestVersion && $user = auth()->user()) {
            UserSetting::setValue($user->id, 'changelog_last_seen_version', $latestVersion);
        }

        return response()->json(['acknowledged' => true]);
    }

    public static function latestVersion(): ?string
    {
        $path = public_path('changelog.json');

        if (! file_exists($path)) {
            return null;
        }

        $entries = json_decode(file_get_contents($path), true) ?? [];

        return $entries[0]['version'] ?? null;
    }
}
