<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class WelcomeController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('Welcome', [
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
            'faqs' => [
                [
                    'question' => 'Is this a one-time purchase?',
                    'answer' => 'Yes. One purchase gives you the full source code with no recurring fees or license restrictions.',
                ],
                [
                    'question' => 'Does it include Stripe billing?',
                    'answer' => 'Yes. Redis-locked Stripe mutations, 4 plan tiers (free, pro, team, enterprise), team seats, dunning emails, and incomplete payment recovery are all included.',
                ],
                [
                    'question' => 'What frontend framework does it use?',
                    'answer' => 'React 18 with TypeScript and Tailwind CSS v4. The entire stack — marketing pages, dashboard, and admin panel — uses React + TypeScript.',
                ],
                [
                    'question' => 'Is there an admin panel?',
                    'answer' => 'Yes. A full admin panel built in React + TypeScript includes user management, billing oversight, audit logs, feature flag toggles, health monitoring, and config viewing.',
                ],
                [
                    'question' => 'What testing is included?',
                    'answer' => '90+ Pest tests (PHP), Vitest tests (React), PHPStan static analysis, Playwright E2E smoke tests, and a CI pipeline via GitHub Actions.',
                ],
            ],
        ]);
    }
}
