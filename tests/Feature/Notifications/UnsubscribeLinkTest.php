<?php

use App\Models\User;
use App\Notifications\DunningReminderNotification;
use App\Notifications\ExpansionNudgeNotification;
use App\Notifications\IncompletePaymentReminder;
use App\Notifications\InvoluntaryChurnWinBackNotification;
use App\Notifications\LimitThresholdNotification;
use App\Notifications\OnboardingReminderNotification;
use App\Notifications\PaymentActionRequiredNotification;
use App\Notifications\PaymentFailedNotification;
use App\Notifications\PaymentRecoveredNotification;
use App\Notifications\ReEngagementNotification;
use App\Notifications\RefundProcessedNotification;
use App\Notifications\TrialEndingNotification;
use App\Notifications\TrialNudgeNotification;
use App\Notifications\UpgradeNudgeNotification;
use App\Notifications\WelcomeSequenceNotification;
use App\Notifications\WinBackNotification;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Assert that the mail message contains an unsubscribe link in its outro lines.
 */
function assertHasUnsubscribeLine(MailMessage $mail, User $user): void
{
    $hasUnsubscribeLink = collect($mail->outroLines)->contains(
        fn ($line) => str_contains((string) $line, 'unsubscribe')
            && str_contains((string) $line, (string) $user->id)
    );

    expect($hasUnsubscribeLink)->toBeTrue(
        'Expected mail outroLines to contain a signed unsubscribe link for user '.$user->id
    );
}

beforeEach(function () {
    config(['features.billing.enabled' => true]);
    ensureCashierTablesExist();
    registerBillingRoutes();
    // The 'unsubscribe' route is always registered via routes/web.php (no feature-flag guard),
    // so URL::signedRoute('unsubscribe', ...) works in all test contexts without manual registration.
});

it('includes unsubscribe link in DunningReminderNotification', function () {
    $user = User::factory()->create();
    $notification = new DunningReminderNotification(emailNumber: 1);

    $mail = $notification->toMail($user);

    assertHasUnsubscribeLine($mail, $user);
});

it('includes unsubscribe link in ExpansionNudgeNotification', function () {
    $user = User::factory()->create();
    $notification = new ExpansionNudgeNotification;

    $mail = $notification->toMail($user);

    assertHasUnsubscribeLine($mail, $user);
});

it('includes unsubscribe link in IncompletePaymentReminder', function () {
    $user = User::factory()->create();
    $notification = new IncompletePaymentReminder(
        confirmUrl: 'https://example.com/confirm',
        hoursRemaining: 12
    );

    $mail = $notification->toMail($user);

    assertHasUnsubscribeLine($mail, $user);
});

it('includes unsubscribe link in InvoluntaryChurnWinBackNotification', function () {
    $user = User::factory()->create();
    $notification = new InvoluntaryChurnWinBackNotification;

    $mail = $notification->toMail($user);

    assertHasUnsubscribeLine($mail, $user);
});

it('includes unsubscribe link in LimitThresholdNotification at threshold', function () {
    $user = User::factory()->create();
    $notification = new LimitThresholdNotification(limitKey: 'projects', threshold: 80);

    $mail = $notification->toMail($user);

    assertHasUnsubscribeLine($mail, $user);
});

it('includes unsubscribe link in LimitThresholdNotification at 100 percent', function () {
    $user = User::factory()->create();
    $notification = new LimitThresholdNotification(limitKey: 'projects', threshold: 100);

    $mail = $notification->toMail($user);

    assertHasUnsubscribeLine($mail, $user);
});

it('includes unsubscribe link in OnboardingReminderNotification', function () {
    $user = User::factory()->create();
    $notification = new OnboardingReminderNotification(emailNumber: 1);

    $mail = $notification->toMail($user);

    assertHasUnsubscribeLine($mail, $user);
});

it('includes unsubscribe link in PaymentActionRequiredNotification', function () {
    $user = User::factory()->create();
    $notification = new PaymentActionRequiredNotification(
        hostedInvoiceUrl: 'https://stripe.com/invoice/test',
        invoiceId: 'in_test123'
    );

    $mail = $notification->toMail($user);

    assertHasUnsubscribeLine($mail, $user);
});

it('includes unsubscribe link in PaymentFailedNotification', function () {
    $user = User::factory()->create();
    $notification = new PaymentFailedNotification(
        invoiceId: 'in_test123',
        subscriptionId: 'sub_test123'
    );

    $mail = $notification->toMail($user);

    assertHasUnsubscribeLine($mail, $user);
});

it('includes unsubscribe link in PaymentRecoveredNotification', function () {
    $user = User::factory()->create();
    $notification = new PaymentRecoveredNotification(invoiceId: 'in_test123');

    $mail = $notification->toMail($user);

    assertHasUnsubscribeLine($mail, $user);
});

it('includes unsubscribe link in ReEngagementNotification', function () {
    $user = User::factory()->create();
    $notification = new ReEngagementNotification(emailNumber: 1);

    $mail = $notification->toMail($user);

    assertHasUnsubscribeLine($mail, $user);
});

it('includes unsubscribe link in RefundProcessedNotification', function () {
    $user = User::factory()->create();
    $notification = new RefundProcessedNotification(
        chargeId: 'ch_test123',
        amountRefunded: 1000
    );

    $mail = $notification->toMail($user);

    assertHasUnsubscribeLine($mail, $user);
});

it('includes unsubscribe link in TrialEndingNotification', function () {
    $user = User::factory()->create();
    $notification = new TrialEndingNotification(daysRemaining: 3);

    $mail = $notification->toMail($user);

    assertHasUnsubscribeLine($mail, $user);
});

it('includes unsubscribe link in TrialNudgeNotification', function () {
    $user = User::factory()->create();
    $notification = new TrialNudgeNotification(emailNumber: 1);

    $mail = $notification->toMail($user);

    assertHasUnsubscribeLine($mail, $user);
});

it('includes unsubscribe link in UpgradeNudgeNotification', function () {
    $user = User::factory()->create();
    $notification = new UpgradeNudgeNotification(score: 75);

    $mail = $notification->toMail($user);

    assertHasUnsubscribeLine($mail, $user);
});

it('includes unsubscribe link in WelcomeSequenceNotification', function () {
    $user = User::factory()->create();
    $notification = new WelcomeSequenceNotification(emailNumber: 1);

    $mail = $notification->toMail($user);

    assertHasUnsubscribeLine($mail, $user);
});

it('includes unsubscribe link in WinBackNotification', function () {
    $user = User::factory()->create();
    $notification = new WinBackNotification(emailNumber: 1);

    $mail = $notification->toMail($user);

    assertHasUnsubscribeLine($mail, $user);
});

it('does not append unsubscribe line for anonymous notifiable without id', function () {
    $anonymous = new stdClass;
    $notification = new PaymentRecoveredNotification(invoiceId: 'in_test123');

    $mail = $notification->toMail($anonymous);

    $hasUnsubscribeLink = collect($mail->outroLines)->contains(
        fn ($line) => str_contains((string) $line, 'unsubscribe')
    );

    expect($hasUnsubscribeLink)->toBeFalse(
        'Expected no unsubscribe link when notifiable has no id'
    );
});
