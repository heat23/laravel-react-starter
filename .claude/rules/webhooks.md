---
description: Webhook signature verification — HMAC-SHA256, incoming/outgoing, Stripe
globs:
  - app/Services/Webhook*
  - app/Services/IncomingWebhook*
  - app/Http/Middleware/VerifyWebhookSignature*
  - app/Http/Controllers/Webhook/**
  - app/Jobs/DispatchWebhookJob*
  - tests/**/Webhook/**
---

# Webhook Signature Verification

**Incoming:** `VerifyWebhookSignature` middleware validates `X-Webhook-Signature` header using HMAC-SHA256 with provider-specific secrets from `config/webhooks.php`. Signature format: `sha256=<hex-digest>` where digest = `hash_hmac('sha256', $rawPayload, $secret)`. Each provider (GitHub, Stripe, custom) has its own secret key.

**Outgoing:** `DispatchWebhookJob` signs payloads with the endpoint's stored secret using same HMAC-SHA256 scheme, sent in `X-Webhook-Signature` header. Recipients verify: `hash_equals(hash_hmac('sha256', $body, $secret), $receivedSignature)`.

**Stripe:** Uses its own signature scheme via Cashier (not our middleware). Stripe webhook route excluded from CSRF since Cashier verifies the Stripe signature internally.

**Adding a new provider:** Add secret to `config/webhooks.php`, create handler in `IncomingWebhookService`, register route in `routes/api.php` with `verify-webhook` middleware.
