<?php

use App\Enums\AuditEvent;

it('has all shared events defined as enum cases', function () {
    foreach (AuditEvent::sharedEvents() as $event) {
        expect(AuditEvent::tryFrom($event))->not->toBeNull(
            "Shared event '{$event}' missing from AuditEvent enum"
        );
    }
});

it('has shared events list matching frontend events.ts values', function () {
    // Extract event string values from the frontend AnalyticsEvents object
    $eventsTs = file_get_contents(resource_path('js/lib/events.ts'));

    // Match all string values in the AnalyticsEvents const object
    preg_match('/export const AnalyticsEvents = \{(.+?)\} as const;/s', $eventsTs, $objectMatch);
    expect($objectMatch)->not->toBeEmpty('Could not find AnalyticsEvents object in events.ts');

    preg_match_all("/:\s*'([^']+)'/", $objectMatch[1], $valueMatches);
    $frontendValues = $valueMatches[1];

    expect($frontendValues)->not->toBeEmpty('Could not extract any event values from events.ts');

    $sharedEvents = AuditEvent::sharedEvents();

    // Every frontend event should be in the shared list
    $missingFromShared = array_diff($frontendValues, $sharedEvents);
    expect($missingFromShared)->toBeEmpty(
        'Frontend events missing from shared list: '.implode(', ', $missingFromShared)
    );

    // Every shared (non-admin) event should be in the frontend
    $nonAdminShared = array_filter($sharedEvents, fn ($e) => ! str_starts_with($e, 'admin.'));
    $missingFromFrontend = array_diff($nonAdminShared, $frontendValues);
    expect($missingFromFrontend)->toBeEmpty(
        'Shared events missing from frontend: '.implode(', ', $missingFromFrontend)
    );
});

it('shared events list covers all non-admin enum cases', function () {
    $sharedEvents = AuditEvent::sharedEvents();
    $allCases = array_map(fn ($case) => $case->value, AuditEvent::cases());
    $nonAdminCases = array_values(array_filter($allCases, fn ($e) => ! str_starts_with($e, 'admin.')));

    $missingFromShared = array_diff($nonAdminCases, $sharedEvents);
    expect($missingFromShared)->toBeEmpty(
        'Non-admin enum cases missing from sharedEvents(): '.implode(', ', $missingFromShared)
    );
});
