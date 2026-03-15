<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class RoadmapController extends Controller
{
    public function index(): Response
    {
        $entries = [];
        $path = public_path('roadmap.json');

        if (file_exists($path)) {
            $entries = json_decode(file_get_contents($path), true) ?? [];
        }

        return Inertia::render('Roadmap', [
            'entries' => $entries,
        ]);
    }
}
