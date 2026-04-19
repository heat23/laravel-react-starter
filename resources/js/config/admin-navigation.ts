import {
  AlertCircle,
  BarChart2,
  Bell,
  Calendar,
  CreditCard,
  Database,
  FileText,
  Heart,
  HeartPulse,
  Inbox,
  Key,
  LayoutDashboard,
  Mail,
  Map,
  MessageSquare,
  Monitor,
  Radio,
  Search,
  Server,
  Settings,
  ShieldCheck,
  Star,
  ToggleLeft,
  Users,
  Users2,
} from 'lucide-react';
import type { LucideIcon } from 'lucide-react';

import type { Features } from '@/types';

export interface AdminNavItem {
  href: string;
  label: string;
  icon: LucideIcon;
  featureFlag?: keyof Features;
}

export interface AdminNavGroup {
  label: string;
  items: AdminNavItem[];
}

export const adminNavigationGroups: AdminNavGroup[] = [
  {
    label: 'Overview',
    items: [
      { href: '/admin', label: 'Metrics', icon: LayoutDashboard },
      { href: '/admin/analytics', label: 'Product Analytics', icon: BarChart2 },
    ],
  },
  {
    label: 'Management',
    items: [
      { href: '/admin/users', label: 'Users', icon: Users },
      { href: '/admin/contact-submissions', label: 'Contact Submissions', icon: Inbox },
      { href: '/admin/nps-responses', label: 'NPS Responses', icon: Star },
      { href: '/admin/feedback', label: 'Feedback', icon: MessageSquare },
      { href: '/admin/roadmap', label: 'Roadmap', icon: Map },
      { href: '/admin/audit-logs', label: 'Audit Logs', icon: FileText },
      { href: '/admin/email-send-logs', label: 'Email Send Logs', icon: Mail },
    ],
  },
  {
    label: 'Features',
    items: [
      {
        href: '/admin/billing',
        label: 'Billing',
        icon: CreditCard,
        featureFlag: 'billing',
      },
      {
        href: '/admin/webhooks',
        label: 'Webhooks',
        icon: Radio,
        featureFlag: 'webhooks',
      },
      {
        href: '/admin/indexnow',
        label: 'IndexNow',
        icon: Search,
        featureFlag: 'indexnow',
      },
      {
        href: '/admin/tokens',
        label: 'API Tokens',
        icon: Key,
        featureFlag: 'apiTokens',
      },
      {
        href: '/admin/social-auth',
        label: 'Social Auth',
        icon: Users2,
        featureFlag: 'socialAuth',
      },
      {
        href: '/admin/notifications',
        label: 'Notifications',
        icon: Bell,
        featureFlag: 'notifications',
      },
      {
        href: '/admin/two-factor',
        label: 'Two-Factor',
        icon: ShieldCheck,
        featureFlag: 'twoFactor',
      },
    ],
  },
  {
    label: 'System',
    items: [
      {
        href: '/admin/feature-flags',
        label: 'Feature Flags',
        icon: ToggleLeft,
      },
      { href: '/admin/sessions', label: 'Sessions', icon: Monitor },
      { href: '/admin/cache', label: 'Cache', icon: Database },
      { href: '/admin/schedule', label: 'Schedule', icon: Calendar },
      { href: '/admin/health', label: 'Health', icon: Heart },
      { href: '/admin/config', label: 'Config', icon: Settings },
      { href: '/admin/system', label: 'System Info', icon: Server },
      { href: '/admin/failed-jobs', label: 'Failed Jobs', icon: AlertCircle },
      { href: '/admin/data-health', label: 'Data Health', icon: HeartPulse },
    ],
  },
];

export function getVisibleAdminGroups(features: Features): AdminNavGroup[] {
  return adminNavigationGroups
    .map((group) => ({
      ...group,
      items: group.items.filter(
        (item) => !item.featureFlag || features[item.featureFlag]
      ),
    }))
    .filter((group) => group.items.length > 0);
}
