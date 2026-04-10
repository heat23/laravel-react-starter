/**
 * Shared type definitions
 *
 * Add your application-specific types here.
 */

// Inertia page props types
export interface User {
  id: number;
  name: string;
  email: string;
  email_verified_at: string | null;
  has_password: boolean;
  is_admin: boolean;
  is_super_admin: boolean;
  two_factor_enabled?: boolean;
}

export interface Auth {
  user: User | null;
  theme?: 'light' | 'dark' | 'system';
  impersonating?: {
    admin_name: string;
  } | null;
}

export interface Features {
  billing: boolean;
  socialAuth: boolean;
  emailVerification: boolean;
  apiTokens: boolean;
  userSettings: boolean;
  notifications: boolean;
  onboarding: boolean;
  apiDocs: boolean;
  twoFactor: boolean;
  webhooks: boolean;
  admin: boolean;
}

export interface UpgradePromptData {
  limit: string;
  plan: string;
  current_plan: string;
  cta_url: string;
}

export interface PageProps {
  auth: Auth;
  flash: {
    success?: string;
    error?: string;
    warning?: string;
    info?: string;
    new_registration?: boolean;
    social_provider?: string;
    upgrade_prompt?: UpgradePromptData | null;
  };
  errors: Record<string, string>;
  features: Features;
  notifications_unread_count: number;
  /** Active A/B experiment variants keyed by experiment name. */
  experiments?: Record<string, string>;
  /** Whether the changelog has entries newer than the user last acknowledged. */
  has_unread_changelog?: boolean;
  /**
   * Billing status for the authenticated user's default subscription.
   * 'past_due' or 'incomplete' when action is needed; null otherwise.
   */
  billing_status?: 'past_due' | 'incomplete' | null;
  /**
   * PQL limit warnings — resources where the user is at ≥80% of their plan limit.
   * Only present when billing is enabled and the user is authenticated.
   */
  limit_warnings?: Record<
    string,
    { current: number; limit: number; threshold: 80 | 100 }
  > | null;
}

// Common pagination type
export interface PaginatedResponse<T> {
  data: T[];
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
  from: number | null;
  to: number | null;
}

// Notifications
export interface AppNotification {
  id: string;
  type: string;
  data: {
    title: string;
    message: string;
    action_url?: string;
    icon?: string;
  };
  read_at: string | null;
  created_at: string;
}

// Webhooks
export interface WebhookEndpoint {
  id: number;
  url: string;
  events: string[];
  description: string | null;
  active: boolean;
  secret?: string;
  deliveries_count?: number;
  created_at: string;
}

export interface WebhookDelivery {
  id: number;
  uuid: string;
  event_type: string;
  status: 'pending' | 'success' | 'failed';
  response_code: number | null;
  attempts: number;
  delivered_at: string | null;
  created_at: string;
}

// Comparison pages
export interface ComparisonFeature {
  feature: string;
  us: string | boolean;
  them: string | boolean;
}

export interface BreadcrumbItem {
  name: string;
  url: string;
}

export interface RelatedComparison {
  name: string;
  slug: string;
  tagline: string;
}

export interface ComparisonPageProps {
  competitor: string;
  competitorName: string;
  title: string;
  metaDescription: string;
  features: ComparisonFeature[];
  lastVerified?: string;
  relatedComparisons?: RelatedComparison[];
  breadcrumbs?: BreadcrumbItem[];
  canonicalUrl?: string;
  appUrl?: string;
  ogImage?: string;
  whenToChooseThem?: string;
}

// Feature landing pages
export interface FeaturePageProps {
  title: string;
  metaDescription: string;
  breadcrumbs?: BreadcrumbItem[];
  canonicalUrl?: string;
  ogImage?: string;
  canRegister?: boolean;
}

// Guide/pillar article pages
export interface GuidePageProps {
  title: string;
  metaDescription: string;
  appName: string;
  breadcrumbs?: BreadcrumbItem[];
  canonicalUrl?: string;
  ogImage?: string;
}

// Form state helpers
export interface FormErrors {
  [key: string]: string;
}
