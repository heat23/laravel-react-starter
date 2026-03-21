// Article content is hardcoded as structured JSX (Option A).
// For a multi-article blog, Option B (Markdown files in resources/content/guides/
// parsed by league/commonmark and passed as HTML props) would be preferable.
// Option B would require DOMPurify.sanitize() on all rendered HTML.

import { ArrowRight } from 'lucide-react';

import { useEffect } from 'react';

import { Head, Link } from '@inertiajs/react';

import { TableOfContents, type TocSection } from '@/Components/blog/TableOfContents';
import { Logo, TextLogo } from '@/Components/branding/Logo';
import { BreadcrumbJsonLd } from '@/Components/seo/BreadcrumbJsonLd';
import { useAnalytics } from '@/hooks/useAnalytics';
import { AnalyticsEvents } from '@/lib/events';
import { Button } from '@/Components/ui/button';
import type { GuidePageProps } from '@/types/index';

interface TwoFactorGuideProps extends GuidePageProps {
    appUrl: string;
}

const sections: TocSection[] = [
    { id: 'why-2fa', title: '1. Why 2FA Matters for SaaS', level: 2 },
    { id: 'totp-vs-sms', title: '2. TOTP vs SMS', level: 2 },
    { id: 'installing-laragear', title: '3. Installing laragear/two-factor', level: 2 },
    { id: 'challenge-controller', title: '4. The 2FA Challenge Controller', level: 2 },
    { id: 'react-ui', title: '5. Building the Setup UI in React', level: 2 },
    { id: 'recovery-codes', title: '6. Recovery Codes', level: 2 },
    { id: 'pest-tests', title: '7. Testing 2FA in Pest', level: 2 },
    { id: 'production', title: '8. Production Considerations', level: 2 },
    { id: 'starter-kit', title: '9. Laravel React Starter Ships This', level: 2 },
];

export default function TwoFactorGuide({ title, metaDescription, appName, appUrl, breadcrumbs }: TwoFactorGuideProps) {
    const { track } = useAnalytics();

    useEffect(() => {
        track(AnalyticsEvents.ENGAGEMENT_PAGE_VIEWED, { page: 'guides-two-factor' });
    }, [track]);

    const canonicalUrl = `${appUrl}/guides/laravel-two-factor-authentication`;

    // Article JSON-LD: author is Person (audit fix SD006), mainEntityOfPage + wordCount added (audit fix SD005)
    // Not wrapped in DOMPurify — JSON-LD is structured data, not user HTML (audit fix SD009)
    const articleSchema = JSON.stringify({
        '@context': 'https://schema.org',
        '@type': 'Article',
        headline: title,
        description: metaDescription,
        author: { '@type': 'Person', name: 'Laravel React Starter' },
        publisher: { '@type': 'Organization', name: appName },
        datePublished: '2026-03-20',
        dateModified: '2026-03-20',
        mainEntityOfPage: { '@type': 'WebPage', '@id': canonicalUrl },
        wordCount: 2400,
    });

    const howToSchema = JSON.stringify({
        '@context': 'https://schema.org',
        '@type': 'HowTo',
        name: 'How to Add Two-Factor Authentication to a Laravel App',
        description: 'Step-by-step guide to adding TOTP-based 2FA in Laravel using laragear/two-factor.',
        step: [
            { '@type': 'HowToStep', position: 1, name: 'Install laragear/two-factor', text: 'Run composer require laragear/two-factor and publish migrations.' },
            { '@type': 'HowToStep', position: 2, name: 'Add trait to User model', text: 'Add the TwoFactorAuthenticatable trait to your User model.' },
            { '@type': 'HowToStep', position: 3, name: 'Register 2FA routes', text: 'Add the challenge and setup routes, gated behind the two_factor feature flag.' },
            { '@type': 'HowToStep', position: 4, name: 'Build the setup UI', text: 'Create a React component with QR code display, code verification, and recovery code reveal.' },
            { '@type': 'HowToStep', position: 5, name: 'Test with Pest', text: 'Write Pest tests covering enable/disable, challenge, recovery codes, and feature flag gating.' },
        ],
    });

    return (
        <>
            <Head title={title}>
                <meta name="description" content={metaDescription} />
                <link rel="canonical" href={canonicalUrl} />
                <meta property="og:title" content={title} />
                <meta property="og:description" content={metaDescription} />
                <meta property="og:type" content="article" />
                <meta name="twitter:card" content="summary_large_image" />
                <meta name="twitter:title" content={title} />
                <meta name="twitter:description" content={metaDescription} />
                {breadcrumbs && <BreadcrumbJsonLd breadcrumbs={breadcrumbs} />}
                <script type="application/ld+json" dangerouslySetInnerHTML={{ __html: articleSchema.replace(/<\/script>/gi, '<\\/script>') }} />
                <script type="application/ld+json" dangerouslySetInnerHTML={{ __html: howToSchema.replace(/<\/script>/gi, '<\\/script>') }} />
            </Head>

            <div className="min-h-screen bg-background">
                <nav className="container flex items-center justify-between py-6">
                    <Link href="/" className="flex items-center gap-2">
                        <Logo className="h-8 w-8" />
                        <TextLogo className="text-xl font-bold" />
                    </Link>
                    <div className="flex items-center gap-4">
                        <Link
                            href="/features/admin-panel"
                            className="text-sm text-muted-foreground transition-colors hover:text-foreground"
                        >
                            Admin Panel
                        </Link>
                        <Link
                            href="/guides/building-saas-with-laravel-12"
                            className="text-sm text-muted-foreground transition-colors hover:text-foreground"
                        >
                            Laravel SaaS Guide
                        </Link>
                        <Link href="/pricing">
                            <Button size="sm">
                                Get Started <ArrowRight className="ml-1 h-4 w-4" />
                            </Button>
                        </Link>
                    </div>
                </nav>

                <main className="container pb-24">
                    <header className="mx-auto max-w-4xl py-12 text-center">
                        <p className="mb-4 text-sm font-medium uppercase tracking-wider text-primary">
                            How-To Guide
                        </p>
                        <h1 className="text-4xl font-bold tracking-tight sm:text-5xl lg:text-6xl">
                            Laravel Two-Factor Authentication Setup &mdash; Complete 2026 Guide with Pest Tests
                        </h1>
                        <p className="mx-auto mt-6 max-w-2xl text-lg text-muted-foreground">
                            This guide shows how to add TOTP-based 2FA to a Laravel app using{' '}
                            <code className="text-sm">laragear/two-factor</code>. You&apos;ll get authenticator app
                            support, recovery codes, and full Pest test coverage. Laravel React Starter ships this
                            pre-configured &mdash;{' '}
                            <Link href="/pricing" className="text-primary hover:underline">
                                skip to the demo
                            </Link>{' '}
                            if you want to see it live.
                        </p>
                        <div className="mt-4 flex items-center justify-center gap-4 text-sm text-muted-foreground">
                            <time dateTime="2026-03-20">March 20, 2026</time>
                            <span aria-hidden="true">&middot;</span>
                            <span>15 min read</span>
                        </div>
                    </header>

                    {/* Two-column layout: article + sticky ToC */}
                    <div className="mx-auto max-w-6xl lg:grid lg:grid-cols-[1fr_250px] lg:gap-12">
                        <div>
                            {/* Mobile ToC */}
                            <TableOfContents sections={sections} />

                            <article className="prose prose-neutral dark:prose-invert max-w-none">

                                {/* Section 1: Why 2FA */}
                                <h2 id="why-2fa">1. Why 2FA Matters for SaaS Applications</h2>
                                <p>
                                    Credential stuffing attacks — where attackers replay leaked username/password pairs from data
                                    breaches — are the most common account takeover vector for SaaS products. A database of 100
                                    million breached credentials is trivially cheap to buy on the dark web, and automated tools
                                    can test them at scale in minutes. Two-factor authentication is the single most effective
                                    control against this class of attack because even a correct password is not enough.
                                </p>
                                <p>
                                    Beyond security, 2FA is increasingly a purchase-gating requirement for B2B SaaS. Enterprise
                                    buyers run security questionnaires before signing contracts, and &ldquo;does your product
                                    support MFA?&rdquo; is a standard checkbox item. If your answer is no, some deals will not
                                    close. For HIPAA, SOC 2, and ISO 27001 compliance, MFA on administrative access is a control
                                    requirement, not a nice-to-have.
                                </p>

                                {/* Section 2: TOTP vs SMS */}
                                <h2 id="totp-vs-sms">2. TOTP vs SMS &mdash; Choosing Your 2FA Approach</h2>
                                <p>
                                    There are two mainstream second factors for SaaS applications: TOTP (Time-based One-Time
                                    Passwords, used by Google Authenticator and Authy) and SMS (sent via Twilio, Vonage, or
                                    similar providers). The choice matters for security, cost, and UX.
                                </p>

                                <div className="not-prose overflow-x-auto">
                                    <table className="w-full border-collapse text-sm">
                                        <thead>
                                            <tr className="border-b">
                                                <th className="py-3 pr-6 text-left font-semibold">Factor</th>
                                                <th className="py-3 pr-6 text-left font-semibold">TOTP (Authenticator App)</th>
                                                <th className="py-3 text-left font-semibold">SMS (Twilio / Vonage)</th>
                                            </tr>
                                        </thead>
                                        <tbody className="divide-y">
                                            <tr>
                                                <td className="py-3 pr-6 font-medium">Cost</td>
                                                <td className="py-3 pr-6 text-muted-foreground">Free (no per-message cost)</td>
                                                <td className="py-3 text-muted-foreground">$0.0075–$0.05 per message</td>
                                            </tr>
                                            <tr>
                                                <td className="py-3 pr-6 font-medium">Phone number required</td>
                                                <td className="py-3 pr-6 text-muted-foreground">No</td>
                                                <td className="py-3 text-muted-foreground">Yes</td>
                                            </tr>
                                            <tr>
                                                <td className="py-3 pr-6 font-medium">Works offline</td>
                                                <td className="py-3 pr-6 text-muted-foreground">Yes</td>
                                                <td className="py-3 text-muted-foreground">No</td>
                                            </tr>
                                            <tr>
                                                <td className="py-3 pr-6 font-medium">SIM swap vulnerability</td>
                                                <td className="py-3 pr-6 text-muted-foreground">No</td>
                                                <td className="py-3 text-muted-foreground">Yes (serious risk)</td>
                                            </tr>
                                            <tr>
                                                <td className="py-3 pr-6 font-medium">User friction</td>
                                                <td className="py-3 pr-6 text-muted-foreground">Low (open app, type 6 digits)</td>
                                                <td className="py-3 text-muted-foreground">Low, but requires signal</td>
                                            </tr>
                                            <tr>
                                                <td className="py-3 pr-6 font-medium">Implementation complexity</td>
                                                <td className="py-3 pr-6 text-muted-foreground">Low (one package)</td>
                                                <td className="py-3 text-muted-foreground">Medium (Twilio account, E.164 formatting, rate limiting)</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <p>
                                    <strong>Recommendation:</strong> TOTP for most SaaS applications. It costs nothing per
                                    verification, works without phone signal, and is not vulnerable to SIM swapping — a social
                                    engineering attack where an attacker convinces a carrier to transfer a victim&apos;s number
                                    to a new SIM. The only case where SMS is worth the tradeoff is when your users are
                                    non-technical and authenticator app setup friction is a real churn concern.
                                </p>

                                {/* Section 3: Installing laragear */}
                                <h2 id="installing-laragear">3. Installing laragear/two-factor</h2>
                                <p>
                                    The <code>laragear/two-factor</code> package handles TOTP generation, QR code rendering,
                                    and recovery codes. Rolling your own TOTP implementation is risky — there are subtle
                                    timing-safe comparison requirements and QR code encoding edge cases that a battle-tested
                                    package handles correctly.
                                </p>

                                <pre><code>{`composer require laragear/two-factor`}</code></pre>

                                <p>Then publish the migrations and config:</p>

                                <pre><code>{`php artisan vendor:publish --provider="Laragear\\TwoFactor\\TwoFactorServiceProvider"`}</code></pre>

                                <p>
                                    Add the <code>TwoFactorAuthenticatable</code> trait to your <code>User</code> model:
                                </p>

                                <pre><code>{`use Laragear\\TwoFactor\\TwoFactorAuthentication;
use Laragear\\TwoFactor\\Contracts\\TwoFactorAuthenticatable;

class User extends Authenticatable implements TwoFactorAuthenticatable
{
    use TwoFactorAuthentication;

    // ...
}`}</code></pre>

                                <p>
                                    The trait adds <code>hasTwoFactorEnabled()</code>, <code>getTwoFactorQrCode()</code>,
                                    <code>getRecoveryCodes()</code>, and <code>useRecoveryCode()</code> methods to your User
                                    model. The package stores TOTP secrets and recovery codes in the{' '}
                                    <code>two_factor_authentications</code> table, keeping them separate from the <code>users</code>{' '}
                                    table.
                                </p>
                                <p>
                                    <strong>Note:</strong> This is already configured in Laravel React Starter. See{' '}
                                    <code>app/Models/User.php</code> for the trait usage and{' '}
                                    <code>database/migrations/</code> for the published migration. The package model
                                    lives at <code>Laragear\TwoFactor\Models\TwoFactorAuthentication</code> — do not create a
                                    local model.
                                </p>

                                {/* Section 4: Challenge Controller */}
                                <h2 id="challenge-controller">4. Adding the 2FA Challenge Controller</h2>
                                <p>
                                    After a user passes password authentication but before they reach the authenticated
                                    application, they need to complete the 2FA challenge. The{' '}
                                    <code>TwoFactorChallengeController</code> handles this flow.
                                </p>

                                <pre><code>{`// app/Http/Controllers/Auth/TwoFactorChallengeController.php

namespace App\\Http\\Controllers\\Auth;

use App\\Http\\Controllers\\Controller;
use Inertia\\Inertia;
use Inertia\\Response;
use Laragear\\TwoFactor\\Http\\Requests\\TwoFactorLoginRequest;

class TwoFactorChallengeController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Auth/TwoFactorChallenge');
    }

    public function store(TwoFactorLoginRequest $request)
    {
        $request->authenticate();

        return redirect()->intended(route('dashboard'));
    }
}`}</code></pre>

                                <p>
                                    The <code>TwoFactorLoginRequest</code> from the package handles code verification —
                                    both TOTP codes and recovery codes — with timing-safe comparisons.
                                </p>

                                <p>Route registration, feature-gated via the <code>two_factor.enabled</code> flag:</p>

                                <pre><code>{`// routes/auth.php (inside the guest middleware group)
if (config('features.two_factor.enabled')) {
    Route::get('/two-factor-challenge', [TwoFactorChallengeController::class, 'create'])
        ->name('two-factor.login');
    Route::post('/two-factor-challenge', [TwoFactorChallengeController::class, 'store']);
}`}</code></pre>

                                <p>
                                    The laragear package provides <code>EnsureTwoFactorAuthenticated</code> middleware, which
                                    intercepts authenticated sessions where 2FA is enabled but not yet completed. Assign it to
                                    your <code>web</code> middleware group or selectively to authenticated route groups.
                                </p>

                                {/* Section 5: React UI */}
                                <h2 id="react-ui">5. Building the Setup UI in React</h2>
                                <p>
                                    The 2FA setup flow has four states: <strong>disabled</strong> (show enable button),
                                    <strong>scanning</strong> (show QR code + verification input),{' '}
                                    <strong>confirming</strong> (show recovery codes, require acknowledgment), and{' '}
                                    <strong>enabled</strong> (show regenerate/disable options).
                                </p>

                                <pre><code>{`// resources/js/Pages/Settings/TwoFactor.tsx

import { useForm } from '@inertiajs/react';
import { LoadingButton } from '@/Components/LoadingButton';
import { QRCodeSVG } from 'qrcode.react';

interface Props {
    enabled: boolean;
    qrCodeUrl: string | null;
    recoveryCodes: string[];
}

export default function TwoFactor({ enabled, qrCodeUrl, recoveryCodes }: Props) {
    const enableForm = useForm({ code: '' });
    const disableForm = useForm({});

    function handleEnable() {
        enableForm.post('/settings/two-factor/enable', {
            onSuccess: () => enableForm.reset(),
        });
    }

    function handleDisable() {
        disableForm.delete('/settings/two-factor', {
            onSuccess: () => disableForm.reset(),
        });
    }

    return (
        <div>
            {!enabled && (
                <div>
                    {qrCodeUrl && (
                        <div>
                            <p>Scan this QR code with your authenticator app:</p>
                            <QRCodeSVG value={qrCodeUrl} size={200} />
                            <input
                                type="text"
                                value={enableForm.data.code}
                                onChange={e => enableForm.setData('code', e.target.value)}
                                placeholder="Enter 6-digit code"
                                maxLength={6}
                                aria-label="Verification code"
                            />
                        </div>
                    )}
                    <LoadingButton loading={enableForm.processing} onClick={handleEnable}>
                        {qrCodeUrl ? 'Confirm Setup' : 'Enable 2FA'}
                    </LoadingButton>
                </div>
            )}

            {enabled && (
                <div>
                    <p>Two-factor authentication is active.</p>
                    <LoadingButton
                        loading={disableForm.processing}
                        variant="destructive"
                        onClick={handleDisable}
                    >
                        Disable 2FA
                    </LoadingButton>
                </div>
            )}
        </div>
    );
}`}</code></pre>

                                <p>
                                    <strong>Critical:</strong> Inertia router calls (<code>router.post</code>,{' '}
                                    <code>router.delete</code>) return immediately — they are not Promises. Always use{' '}
                                    <code>onSuccess</code> and <code>onError</code> callbacks for post-request state updates,
                                    not <code>await</code>. The <code>LoadingButton</code> component handles the{' '}
                                    <code>processing</code> state automatically.
                                </p>

                                {/* Section 6: Recovery Codes */}
                                <h2 id="recovery-codes">6. Recovery Codes</h2>
                                <p>
                                    Recovery codes solve the &ldquo;lost phone&rdquo; problem: if a user loses access to their
                                    authenticator app, they need a fallback to regain account access without locking themselves
                                    out permanently. Without recovery codes, the only escape is an admin intervention — which
                                    does not scale.
                                </p>
                                <p>
                                    The <code>laragear/two-factor</code> package generates recovery codes automatically when 2FA
                                    is enabled. The default format is 8 codes, each 10 characters, in groups of 5 characters
                                    separated by a dash: <code>ABCDE-FGHIJ</code>. Each code is hashed before storage and
                                    marked as used after the first successful authentication — they are single-use by design.
                                </p>

                                <pre><code>{`// Accessing recovery codes on the User model
$user->getRecoveryCodes();
// Returns: ['ABCDE-FGHIJ', 'KLMNO-PQRST', ...]

// Mark a code as used after authentication
$user->useRecoveryCode('ABCDE-FGHIJ');

// Regenerate all codes (existing codes invalidated)
$user->generateRecoveryCodes();`}</code></pre>

                                <p>
                                    In your settings UI, show recovery codes once after setup with a show/hide toggle and a
                                    &ldquo;Download&rdquo; button that saves them as a text file. Make the regeneration flow
                                    require re-entering the current TOTP code to prevent unauthorized code invalidation.
                                </p>

                                {/* Section 7: Pest Tests */}
                                <h2 id="pest-tests">7. Testing 2FA in Pest</h2>
                                <p>
                                    Testing 2FA requires covering: the happy path (enable, challenge, disable), recovery codes,
                                    and the feature flag gate. Here&apos;s the Pest test structure:
                                </p>

                                <pre><code>{`// tests/Feature/Auth/TwoFactorTest.php

use App\\Models\\User;
use Laragear\\TwoFactor\\Models\\TwoFactorAuthentication;

it('enables 2FA and creates a TwoFactorAuthentication record', function () {
    $user = User::factory()->create();

    $response = actingAs($user)
        ->post('/settings/two-factor/enable');

    $response->assertRedirect();
    expect($user->fresh()->hasTwoFactorEnabled())->toBeFalse(); // not yet confirmed

    // After QR code shown, user submits a valid TOTP code to confirm
    // (In tests, generate a code from the user's 2FA secret)
    $auth = TwoFactorAuthentication::where('authenticatable_id', $user->id)->first();
    expect($auth)->not->toBeNull();
});

it('challenge page redirects unauthenticated users', function () {
    get('/two-factor-challenge')->assertRedirect('/login');
});

it('valid TOTP code passes the challenge', function () {
    $user = User::factory()->withTwoFactor()->create();

    // Use the package's test helper to generate a valid code
    $code = $user->makeTwoFactorCode();

    actingAs($user, 'web') // partial auth (pre-2FA)
        ->post('/two-factor-challenge', ['code' => $code])
        ->assertRedirect(route('dashboard'));
});

it('recovery code can be used once', function () {
    $user = User::factory()->withTwoFactor()->create();
    $code = $user->getRecoveryCodes()[0];

    actingAs($user, 'web')
        ->post('/two-factor-challenge', ['recovery_code' => $code])
        ->assertRedirect(route('dashboard'));

    // Code is now spent — second attempt fails
    actingAs($user, 'web')
        ->post('/two-factor-challenge', ['recovery_code' => $code])
        ->assertSessionHasErrors();
});

it('returns 404 when two_factor feature flag is disabled', function () {
    // Note: feature flags set at boot time cannot be toggled mid-suite.
    // Test the disabled case in a separate test group with overridden config.
    config(['features.two_factor.enabled' => false]);

    // Verify the route does not resolve when flag is off
    expect(Route::has('two-factor.login'))->toBeFalse();
})->skip('Route registration is boot-time — see CLAUDE.md > Testing Gotchas');`}</code></pre>

                                <p>
                                    <strong>Note on feature flag testing:</strong> Routes gated with{' '}
                                    <code>if (config('features.two_factor.enabled'))</code> are registered at application boot.
                                    The <code>phpunit.xml</code> env config determines which routes are available for the entire
                                    test suite run. Test the enabled behavior directly; test disabled behavior in a dedicated
                                    integration test that boots the application with the flag explicitly set to false.
                                </p>

                                {/* Section 8: Production */}
                                <h2 id="production">8. Production Considerations</h2>
                                <p>
                                    <strong>Enforce 2FA for admin users.</strong> Add a middleware that checks{' '}
                                    <code>{'$user->hasTwoFactorEnabled()'}</code> on the admin route group. If an admin has not
                                    enabled 2FA, redirect them to the 2FA setup page rather than the admin panel.
                                </p>

                                <pre><code>{`// Enforce 2FA for admin users in route middleware
Route::middleware(['auth', 'verified', 'admin', 'require-2fa'])
    ->prefix('admin')
    ->group(function () {
        // Admin routes
    });`}</code></pre>

                                <p>
                                    <strong>Show a 2FA warning on sensitive pages.</strong> On billing and account settings
                                    pages, show a dismissible banner if the user has 2FA disabled. Keep it advisory, not
                                    blocking — forced enrollment on existing users causes support tickets.
                                </p>
                                <p>
                                    <strong>Recovery code regeneration.</strong> After a recovery code is used to log in, show
                                    a one-time prompt asking the user to regenerate their codes. A used recovery code means one
                                    fewer safety net — users should know.
                                </p>
                                <p>
                                    <strong>Lost device and lost codes.</strong> Document your account recovery process before
                                    you launch. The fallback when a user loses both their device and their recovery codes is a
                                    manual identity verification via email confirmation with a waiting period (e.g., 24 hours).
                                    Never bypass 2FA for a user without identity verification — that&apos;s a social engineering
                                    attack surface.
                                </p>

                                {/* Section 9: Starter Kit */}
                                <h2 id="starter-kit">9. Laravel React Starter Ships This Out of the Box</h2>
                                <p>
                                    Two-factor authentication is pre-configured in Laravel React Starter, feature-gated via{' '}
                                    <code>FEATURE_TWO_FACTOR=true</code> in your <code>.env</code>. Enable it, and the settings
                                    UI, challenge controller, routes, and Pest tests are all live immediately. The{' '}
                                    <Link href="/features/admin-panel" className="text-primary hover:underline">
                                        admin panel
                                    </Link>{' '}
                                    shows which users have 2FA enabled, giving you visibility into your security posture.
                                </p>
                                <p>
                                    For a broader picture of how 2FA fits into a full Laravel SaaS, see the{' '}
                                    <Link
                                        href="/guides/building-saas-with-laravel-12"
                                        className="text-primary hover:underline"
                                    >
                                        Complete Guide to Building a SaaS with Laravel 12
                                    </Link>
                                    .
                                </p>

                                <div className="not-prose mt-12 rounded-lg border bg-muted/30 p-8 text-center">
                                    <h3 className="text-xl font-bold">Skip the setup — it&apos;s already built</h3>
                                    <p className="mt-2 text-muted-foreground">
                                        Laravel React Starter includes 2FA, billing, admin panel, webhooks, and 90+ tests.
                                        Enable features with a single env var.
                                    </p>
                                    <Link href="/pricing" className="mt-6 inline-block">
                                        <Button size="lg">
                                            View Pricing <ArrowRight className="ml-2 h-4 w-4" />
                                        </Button>
                                    </Link>
                                </div>
                            </article>
                        </div>

                        {/* Desktop sticky ToC */}
                        <aside className="hidden lg:block">
                            <div className="sticky top-8">
                                <TableOfContents sections={sections} sticky />
                            </div>
                        </aside>
                    </div>
                </main>

                <footer className="border-t py-12">
                    <div className="container flex flex-col items-center justify-between gap-4 sm:flex-row">
                        <p className="text-sm text-muted-foreground">
                            &copy; {new Date().getFullYear()} {appName}
                        </p>
                        <div className="flex gap-6">
                            <Link href="/privacy" className="text-sm text-muted-foreground hover:text-foreground">
                                Privacy
                            </Link>
                            <Link href="/terms" className="text-sm text-muted-foreground hover:text-foreground">
                                Terms
                            </Link>
                            <Link href="/contact" className="text-sm text-muted-foreground hover:text-foreground">
                                Contact
                            </Link>
                        </div>
                    </div>
                </footer>
            </div>
        </>
    );
}
