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

export interface PageProps {
  auth: Auth;
  flash: {
    success?: string;
    error?: string;
  };
  errors: Record<string, string>;
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

// Form state helpers
export interface FormErrors {
  [key: string]: string;
}
