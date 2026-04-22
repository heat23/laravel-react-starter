<?php

use App\Models\AuditLog;

// ============================================
// anonymizeIp() — IPv4
// ============================================

test('anonymizeIp zeroes last octet of IPv4 address', function () {
    expect(AuditLog::anonymizeIp('192.168.1.100'))->toBe('192.168.1.0');
});

test('anonymizeIp handles IPv4 address ending in zero', function () {
    expect(AuditLog::anonymizeIp('10.0.0.0'))->toBe('10.0.0.0');
});

test('anonymizeIp handles IPv4 address with max last octet', function () {
    expect(AuditLog::anonymizeIp('172.16.254.255'))->toBe('172.16.254.0');
});

test('anonymizeIp handles loopback IPv4', function () {
    expect(AuditLog::anonymizeIp('127.0.0.1'))->toBe('127.0.0.0');
});

// ============================================
// anonymizeIp() — IPv6
// ============================================

test('anonymizeIp zeroes last 80 bits of IPv6 address', function () {
    // Full IPv6: 2001:0db8:85a3:0000:0000:8a2e:0370:7334
    // First 48 bits (3 groups) preserved: 2001:db8:85a3
    // Last 80 bits (5 groups) zeroed: 0000:0000:0000:0000:0000
    $result = AuditLog::anonymizeIp('2001:0db8:85a3:0000:0000:8a2e:0370:7334');
    expect($result)->toBe('2001:db8:85a3::');
});

test('anonymizeIp handles compressed IPv6', function () {
    $result = AuditLog::anonymizeIp('::1');
    // ::1 expanded = 0000:0000:0000:0000:0000:0000:0000:0001
    // After zeroing last 80 bits: 0000:0000:0000:0000:0000:0000:0000:0000 = ::
    expect($result)->toBe('::');
});

test('anonymizeIp handles IPv6 loopback', function () {
    expect(AuditLog::anonymizeIp('::1'))->toBe('::');
});

// ============================================
// anonymizeIp() — IPv4-mapped IPv6
// ============================================

test('anonymizeIp masks IPv4-mapped IPv6 address', function () {
    // Dual-stack servers commonly pass client IPs as ::ffff:x.x.x.x
    $result = AuditLog::anonymizeIp('::ffff:192.168.1.42');
    expect($result)->toBe('::ffff:192.168.1.0');
});

test('anonymizeIp masks IPv4-mapped IPv6 with different octets', function () {
    $result = AuditLog::anonymizeIp('::ffff:10.0.0.1');
    expect($result)->toBe('::ffff:10.0.0.0');
});

test('anonymizeIp handles uppercase FFFF prefix in IPv4-mapped IPv6', function () {
    $result = AuditLog::anonymizeIp('::FFFF:192.168.1.42');
    expect($result)->toBe('::FFFF:192.168.1.0');
});

test('anonymizeIp handles mixed-case ffff prefix in IPv4-mapped IPv6', function () {
    $result = AuditLog::anonymizeIp('::FfFf:10.20.30.40');
    expect($result)->toBe('::FfFf:10.20.30.0');
});

test('anonymizeIp returns masked placeholder for invalid embedded IPv4 in mapped address', function () {
    // ::ffff:999.0.0.1 matches the digit pattern but 999 is not a valid IPv4 octet.
    // Must not fall through to the IPv6 branch or return the raw address.
    $result = AuditLog::anonymizeIp('::ffff:999.0.0.1');
    expect($result)->toBe('::ffff:0.0.0.0');
});

test('anonymizeIp returns masked placeholder for another invalid embedded IPv4', function () {
    $result = AuditLog::anonymizeIp('::ffff:256.1.1.1');
    expect($result)->toBe('::ffff:0.0.0.0');
});

// ============================================
// anonymizeIp() — edge cases
// ============================================

test('anonymizeIp returns null for null input', function () {
    expect(AuditLog::anonymizeIp(null))->toBeNull();
});

test('anonymizeIp returns empty string for empty input', function () {
    expect(AuditLog::anonymizeIp(''))->toBe('');
});

// ============================================
// isSecurityEvent()
// ============================================

test('isSecurityEvent returns true for auth events', function (string $event) {
    expect(AuditLog::isSecurityEvent($event))->toBeTrue();
})->with([
    'auth.login',
    'auth.logout',
    'auth.register',
    'auth.2fa_enabled',
    'auth.social_login',
    'auth.password_changed',
]);

test('isSecurityEvent returns true for unauthorized access attempts', function () {
    expect(AuditLog::isSecurityEvent('admin.unauthorized_access_attempt'))->toBeTrue();
});

test('isSecurityEvent returns false for non-security events', function (string $event) {
    expect(AuditLog::isSecurityEvent($event))->toBeFalse();
})->with([
    'profile.updated',
    'subscription.created',
    'onboarding.completed',
    'admin.toggle_admin',
    'admin.user_deactivated',
    'admin.cache_flushed',
    'contact.submitted',
]);
