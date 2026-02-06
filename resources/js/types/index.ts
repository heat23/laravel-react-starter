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
}

export interface Auth {
  user: User | null;
  theme?: 'light' | 'dark' | 'system';
}

export interface Features {
  billing: boolean;
  socialAuth: boolean;
  emailVerification: boolean;
  apiTokens: boolean;
  userSettings: boolean;
  notifications: boolean;
  onboarding: boolean;
}

export interface PageProps {
  auth: Auth;
  flash: {
    success?: string;
    error?: string;
  };
  errors: Record<string, string>;
  features: Features;
  notifications_unread_count: number;
}

// Common pagination type
export interface PaginatedResponse<T> {
  data: T[];
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
  from: number;
  to: number;
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

// Form state helpers
export interface FormErrors {
  [key: string]: string;
}
