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
            'appUrl' => rtrim(config('app.url'), '/'),
            'userCount' => 100,
            'testimonials' => [
                [
                    'quote' => 'Saved me 2 months of boilerplate. The double-charge prevention alone is worth the price.',
                    'name' => 'Alex M.',
                    'role' => 'Senior Developer, Solo SaaS Founder',
                ],
                [
                    'quote' => 'Finally, a Laravel starter that actually includes tests. 90+ tests meant I could refactor confidently from day one.',
                    'name' => 'Sarah K.',
                    'role' => 'Solo Founder',
                ],
                [
                    'quote' => 'We use this as our agency base for every client project. Feature flags let us customize scope per engagement.',
                    'name' => 'James T.',
                    'role' => 'Agency Lead',
                ],
            ],
            'faqs' => [
                [
                    'question' => 'Is this just another Laravel boilerplate?',
                    'answer' => 'No. Most boilerplates give you scaffolding and leave you to figure out billing race conditions, feature flag resolution, and admin panel security. This starter kit includes 90+ tests, concurrent payment protection, and a same-stack React admin panel — all production-tested.',
                ],
                [
                    'question' => 'Can I use this commercially?',
                    'answer' => 'Yes. You get full source code ownership. Use it for client projects, your own SaaS, or internal tools — no attribution required.',
                ],
                [
                    'question' => 'What if I don\'t need all the features?',
                    'answer' => 'That\'s what the 11 feature flags are for. Disable billing, webhooks, or any feature with a single environment variable. Disabled features don\'t register routes or render UI.',
                ],
                [
                    'question' => 'How is this different from Jetstream or Breeze?',
                    'answer' => 'Breeze gives you auth scaffolding. Jetstream adds teams. This gives you auth + billing + admin panel + feature flags + 90+ tests + email sequences — all in the same React + TypeScript stack, not Livewire.',
                ],
                [
                    'question' => 'Do I need Redis?',
                    'answer' => 'Redis is required for billing mutation locks (prevents double-charges). For development, a local Redis instance works. In production, any Redis-compatible service (AWS ElastiCache, Upstash) works.',
                ],
                [
                    'question' => 'How do updates work?',
                    'answer' => 'You own the full source code. Updates are delivered as GitHub releases — you review the diff and merge what makes sense for your project. There\'s no lock-in.',
                ],
                [
                    'question' => 'What\'s included in the free tier?',
                    'answer' => 'The free plan is pre-configured out of the box. Users on the free plan get access to features you define — billing, seat limits, and plan-gated routes are all configurable in config/plans.php.',
                ],
            ],
        ]);
    }
}
