---
description: Webhook signature verification — HMAC-SHA256, incoming/outgoing, Stripe
globs:
  - app/Services/Webhook*
  - app/Services/IncomingWebhook*
  - app/Http/Middleware/VerifyWebhookSignature*
  - app/Http/Controllers/Webhook/**
  - app/Webhooks/**
  - app/Jobs/DispatchWebhookJob*
  - tests/**/Webhook/**
  - tests/**/Webhooks/**
---

# Webhook Signature Verification

**Incoming:** `VerifyWebhookSignature` middleware resolves a `WebhookProvider` class from `config/webhooks.php` keyed by the `{provider}` route parameter, calls `$provider->verify($request, $rawPayload)`, then attaches the provider to `$request->attributes` for the controller. Provider classes live in `app/Webhooks/Providers/`.

**Outgoing:** `DispatchWebhookJob` signs payloads with the endpoint's stored secret using HMAC-SHA256, sent in `X-Webhook-Signature` header. Recipients verify: `hash_equals(hash_hmac('sha256', $body, $secret), $receivedSignature)`.

**Stripe:** Uses its own signature scheme via Cashier (not our middleware). Billing events arrive at `/stripe/webhook` and are handled by `StripeWebhookController` → `StripeEventMap` → per-event handlers in `app/Webhooks/Stripe/Handlers/`.

**Adding a new incoming provider:**
1. Create `app/Webhooks/Providers/YourProvider.php` implementing `WebhookProvider`
2. Register in `config/webhooks.php` under `incoming.providers.{key}.class`
3. The route `POST /api/webhooks/incoming/{provider}` is already registered — no route change needed
