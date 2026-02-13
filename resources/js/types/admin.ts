import type { PaginatedResponse } from "@/types";

// ---------------------------------------------------------------------------
// Shared / Reusable
// ---------------------------------------------------------------------------

/** Common chart data point: date + count */
export interface ChartDataPoint {
  date: string;
  count: number;
}

/** Shared filter state for sortable/searchable tables */
export interface SortableFilters {
  search?: string;
  sort?: string;
  dir?: string;
}

// ---------------------------------------------------------------------------
// Audit Logs
// ---------------------------------------------------------------------------

/** Minimal audit log used in recent-activity tables */
export interface AuditLogSummary {
  id: number;
  event: string;
  user_name: string | null;
  user_email?: string | null;
  ip?: string | null;
  created_at: string;
}

/** Audit log entry used in user-detail / billing-detail contexts */
export interface AuditLogEntry {
  id: number;
  event: string;
  ip: string | null;
  created_at: string;
  metadata: Record<string, unknown> | null;
}

/** Full audit log row used in audit log list page */
export interface AuditLogRow {
  id: number;
  event: string;
  user_name: string | null;
  user_email: string | null;
  user_id: number | null;
  ip: string | null;
  user_agent: string | null;
  metadata: Record<string, unknown> | null;
  created_at: string;
}

/** Single audit log detail */
export interface AuditLogDetail {
  id: number;
  event: string;
  user_id: number | null;
  user_name: string | null;
  user_email: string | null;
  ip: string | null;
  user_agent: string | null;
  metadata: Record<string, unknown> | null;
  created_at: string;
}

// ---------------------------------------------------------------------------
// Users
// ---------------------------------------------------------------------------

/** User row in admin user list */
export interface AdminUser {
  id: number;
  name: string;
  email: string;
  is_admin: boolean;
  email_verified_at: string | null;
  last_login_at: string | null;
  created_at: string;
  tokens_count: number;
  deleted_at: string | null;
}

/** Extended user detail for admin user show page */
export interface AdminUserDetail extends AdminUser {
  signup_source: string | null;
  has_password: boolean;
}

export interface AdminUserFilters extends SortableFilters {
  admin?: string;
}

// ---------------------------------------------------------------------------
// Billing
// ---------------------------------------------------------------------------

/** Dashboard-level billing stats */
export interface BillingDashboardStats {
  active_subscriptions: number;
  trialing: number;
  past_due: number;
  canceled: number;
  total_ever: number;
  mrr: number;
  churn_rate: number;
  trial_conversion_rate: number;
}

export interface BillingTrialStats {
  active_trials: number;
  expiring_soon: number;
}

export interface TierDistribution {
  tier: string;
  count: number;
}

export interface StatusBreakdown {
  status: string;
  count: number;
}

/** Billing event in recent events table */
export interface BillingEvent {
  id: number;
  event: string;
  user_name: string | null;
  metadata: Record<string, unknown> | null;
  created_at: string;
}

/** Subscription row in admin subscriptions list */
export interface SubscriptionRow {
  id: number;
  user_id: number;
  user_name: string;
  user_email: string;
  stripe_status: string;
  tier: string;
  quantity: number;
  trial_ends_at: string | null;
  ends_at: string | null;
  created_at: string;
}

/** Subscription detail for admin billing show page */
export interface SubscriptionDetail {
  id: number;
  user_name: string;
  user_email: string;
  user_id: number;
  stripe_id: string;
  stripe_status: string;
  tier: string;
  quantity: number;
  trial_ends_at: string | null;
  ends_at: string | null;
  created_at: string;
}

export interface SubscriptionItem {
  id: number;
  stripe_price: string;
  stripe_product: string;
  quantity: number;
  tier: string;
}

export interface SubscriptionFilters extends SortableFilters {
  status?: string;
  tier?: string;
}

/** User subscription as shown on user detail page */
export interface UserSubscription {
  stripe_status: string;
  stripe_price: string;
  quantity: number;
  trial_ends_at: string | null;
}

// ---------------------------------------------------------------------------
// Webhooks
// ---------------------------------------------------------------------------

export interface WebhookDashboardStats {
  total_endpoints: number;
  active_endpoints: number;
  total_deliveries: number;
  successful_deliveries: number;
  failed_deliveries: number;
  pending_deliveries: number;
  failure_rate: number;
  total_incoming: number;
  incoming_by_provider: Record<string, number>;
}

export interface WebhookDeliveryChartPoint {
  date: string;
  success: number;
  failed: number;
}

export interface WebhookFailure {
  id: number;
  event_type: string;
  endpoint_url: string;
  response_code: number | null;
  attempts: number;
  created_at: string;
}

// ---------------------------------------------------------------------------
// API Tokens
// ---------------------------------------------------------------------------

export interface TokenDashboardStats {
  total_tokens: number;
  users_with_tokens: number;
  recently_used: number;
  expired_tokens: number;
  never_used: number;
}

export interface ActiveToken {
  token_name: string;
  last_used_at: string;
  abilities: string[];
  user_name: string;
  user_email: string;
}

// ---------------------------------------------------------------------------
// Social Auth
// ---------------------------------------------------------------------------

export interface SocialAuthStats {
  total_linked: number;
  users_with_social: number;
  by_provider: Record<string, number>;
  expired_tokens: number;
}

// ---------------------------------------------------------------------------
// Two-Factor
// ---------------------------------------------------------------------------

export interface TwoFactorStats {
  total_users: number;
  two_factor_enabled: number;
  adoption_rate: number;
  without_two_factor: number;
}

// ---------------------------------------------------------------------------
// Notifications
// ---------------------------------------------------------------------------

export interface NotificationStats {
  total_sent: number;
  unread: number;
  read: number;
  read_rate: number;
  sent_last_7d: number;
  by_type: Array<{ type: string; count: number }>;
}

// ---------------------------------------------------------------------------
// Health
// ---------------------------------------------------------------------------

export interface HealthCheck {
  status: string;
  message: string;
  response_time_ms: number;
}

export interface HealthStatus {
  status: "healthy" | "degraded" | "unhealthy";
  checks: Record<string, HealthCheck>;
  timestamp: string;
}

// ---------------------------------------------------------------------------
// System
// ---------------------------------------------------------------------------

export interface SystemInfo {
  php_version: string;
  laravel_version: string;
  node_version: string | null;
  server: {
    os: string;
    server_software: string;
  };
  database: {
    driver: string;
    version: string | null;
  };
  queue: {
    driver: string;
    pending_jobs: number | null;
    failed_jobs: number | null;
  };
  packages: Array<{ name: string; version: string }>;
}

// ---------------------------------------------------------------------------
// Config
// ---------------------------------------------------------------------------

export interface FeatureFlag {
  key: string;
  enabled: boolean;
  env_var: string;
}

export interface ConfigWarning {
  level: "critical" | "warning";
  message: string;
}

export interface EnvironmentConfig {
  app_env: string;
  timezone: string;
}

// ---------------------------------------------------------------------------
// Dashboard (main admin overview)
// ---------------------------------------------------------------------------

export interface AdminDashboardStats {
  total_users: number;
  new_users_7d: number;
  new_users_30d: number;
  admin_count: number;
  active_subscriptions?: number;
}

// ---------------------------------------------------------------------------
// Page Props (one per admin page)
// ---------------------------------------------------------------------------

export interface AdminDashboardProps {
  stats: AdminDashboardStats;
  signup_chart: ChartDataPoint[];
  recent_activity: AuditLogSummary[];
}

export interface AdminUsersIndexProps {
  users: PaginatedResponse<AdminUser>;
  filters: AdminUserFilters;
}

export interface AdminUsersShowProps {
  user: AdminUserDetail;
  recent_audit_logs: AuditLogEntry[];
  subscription: UserSubscription | null;
}

export interface AdminAuditLogsIndexProps {
  logs: PaginatedResponse<AuditLogRow>;
  eventTypes: string[];
  filters: {
    event?: string;
    user_id?: string;
    from?: string;
    to?: string;
  };
}

export interface AdminAuditLogShowProps {
  auditLog: AuditLogDetail;
}

export interface AdminBillingDashboardProps {
  stats: BillingDashboardStats;
  tier_distribution: TierDistribution[];
  status_breakdown: StatusBreakdown[];
  growth_chart: ChartDataPoint[];
  trial_stats: BillingTrialStats;
  recent_events: BillingEvent[];
}

export interface AdminBillingSubscriptionsProps {
  subscriptions: PaginatedResponse<SubscriptionRow>;
  filters: SubscriptionFilters;
  statuses: string[];
  tiers: string[];
}

export interface AdminBillingShowProps {
  subscription: SubscriptionDetail;
  items: SubscriptionItem[];
  audit_logs: AuditLogEntry[];
}

export interface AdminHealthProps {
  health: HealthStatus;
}

export interface AdminConfigProps {
  feature_flags: FeatureFlag[];
  warnings: ConfigWarning[];
  environment: EnvironmentConfig;
}

export interface AdminSystemProps {
  system: SystemInfo;
}

export interface AdminWebhooksDashboardProps {
  stats: WebhookDashboardStats;
  delivery_chart: WebhookDeliveryChartPoint[];
  recent_failures: WebhookFailure[];
}

export interface AdminTokensDashboardProps {
  stats: TokenDashboardStats;
  most_active: ActiveToken[];
}

export interface AdminSocialAuthDashboardProps {
  stats: SocialAuthStats;
}

export interface AdminTwoFactorDashboardProps {
  stats: TwoFactorStats;
}

export interface AdminNotificationsDashboardProps {
  stats: NotificationStats;
  volume_chart: ChartDataPoint[];
}

// ---------------------------------------------------------------------------
// Feature Flags
// ---------------------------------------------------------------------------

/** Feature flag summary for admin display */
export interface FeatureFlagAdmin {
  flag: string;
  env_default: boolean;
  global_override: boolean | null;
  effective: boolean;
  user_override_count: number;
  is_protected: boolean;
  is_route_dependent: boolean;
}

/** User override for a feature flag */
export interface FeatureFlagUserOverride {
  user_id: number;
  name: string;
  email: string;
  enabled: boolean;
}

/** User search result for targeting */
export interface FeatureFlagUserSearch {
  id: number;
  name: string;
  email: string;
}

export interface AdminFeatureFlagsIndexProps {
  flags: FeatureFlagAdmin[];
}
