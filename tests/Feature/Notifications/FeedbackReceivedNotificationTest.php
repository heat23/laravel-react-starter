<?php

use App\Models\Feedback;
use App\Models\User;
use App\Notifications\FeedbackReceivedNotification;

function makeFeedbackUser(string $name, string $email): User
{
    $user = new User;
    $user->name = $name;
    $user->email = $email;

    return $user;
}

function makeFeedbackItem(string $type, string $message): Feedback
{
    $feedback = new Feedback;
    $feedback->type = $type;
    $feedback->message = $message;

    return $feedback;
}

it('builds mail with correct subject and body', function () {
    $user = makeFeedbackUser('Alice', 'alice@example.com');
    $feedback = makeFeedbackItem('bug', 'Something is broken.');
    $feedback->setRelation('user', $user);

    $notification = new FeedbackReceivedNotification($feedback);
    $mail = $notification->toMail(new stdClass);

    expect($mail->subject)->toContain('Bug Feedback from Alice');
    expect($mail->introLines)->toContain('**From:** Alice (alice@example.com)');
});

it('uses Guest when no user is attached', function () {
    $feedback = makeFeedbackItem('general', 'Anonymous feedback.');
    $feedback->setRelation('user', null);

    $notification = new FeedbackReceivedNotification($feedback);
    $mail = $notification->toMail(new stdClass);

    expect($mail->subject)->toContain('Guest');
    expect(implode(' ', $mail->introLines))->toContain('Guest');
});

it('strips CRLF from subject when userName contains injection attempt', function () {
    $user = makeFeedbackUser("Alice\r\nBcc: evil@example.com", 'alice@example.com');
    $feedback = makeFeedbackItem('bug', 'Injection test.');
    $feedback->setRelation('user', $user);

    $notification = new FeedbackReceivedNotification($feedback);
    $mail = $notification->toMail(new stdClass);

    expect($mail->subject)->not->toContain("\r")->not->toContain("\n");
});

it('strips CRLF from subject when type contains injection attempt', function () {
    $feedback = makeFeedbackItem("bug\r\nBcc: evil@example.com", 'Type injection test.');
    $feedback->setRelation('user', null);

    $notification = new FeedbackReceivedNotification($feedback);
    $mail = $notification->toMail(new stdClass);

    expect($mail->subject)->not->toContain("\r")->not->toContain("\n");
});

it('strips CRLF from user email in body to prevent injection', function () {
    $user = makeFeedbackUser('Alice', "alice@example.com\r\nX-Injected: header");
    $feedback = makeFeedbackItem('general', 'Email injection test.');
    $feedback->setRelation('user', $user);

    $notification = new FeedbackReceivedNotification($feedback);
    $mail = $notification->toMail(new stdClass);

    foreach ($mail->introLines as $line) {
        expect($line)->not->toContain("\r")->not->toContain("\n");
    }
});

it('falls back to Unknown when sanitized name is empty after stripping HTML', function () {
    $user = makeFeedbackUser('<script>alert(1)</script>', 'alice@example.com');
    $feedback = makeFeedbackItem('bug', 'Name sanitization test.');
    $feedback->setRelation('user', $user);

    $notification = new FeedbackReceivedNotification($feedback);
    $mail = $notification->toMail(new stdClass);

    expect($mail->subject)->toContain('Unknown');
    expect(implode(' ', $mail->introLines))->toContain('Unknown');
});
