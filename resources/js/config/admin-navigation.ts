import {
    Bell,
    CreditCard,
    FileText,
    Heart,
    Key,
    LayoutDashboard,
    Radio,
    Server,
    Settings,
    ShieldCheck,
    ToggleLeft,
    Users,
    Users2,
} from "lucide-react";
import type { LucideIcon } from "lucide-react";

import type { Features } from "@/types";

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
        label: "Overview",
        items: [
            { href: "/admin", label: "Metrics", icon: LayoutDashboard },
        ],
    },
    {
        label: "Management",
        items: [
            { href: "/admin/users", label: "Users", icon: Users },
            { href: "/admin/audit-logs", label: "Audit Logs", icon: FileText },
        ],
    },
    {
        label: "Features",
        items: [
            { href: "/admin/billing", label: "Billing", icon: CreditCard, featureFlag: "billing" },
            { href: "/admin/webhooks", label: "Webhooks", icon: Radio, featureFlag: "webhooks" },
            { href: "/admin/tokens", label: "API Tokens", icon: Key, featureFlag: "apiTokens" },
            { href: "/admin/social-auth", label: "Social Auth", icon: Users2, featureFlag: "socialAuth" },
            { href: "/admin/notifications", label: "Notifications", icon: Bell, featureFlag: "notifications" },
            { href: "/admin/two-factor", label: "Two-Factor", icon: ShieldCheck, featureFlag: "twoFactor" },
        ],
    },
    {
        label: "System",
        items: [
            { href: "/admin/feature-flags", label: "Feature Flags", icon: ToggleLeft },
            { href: "/admin/health", label: "Health", icon: Heart },
            { href: "/admin/config", label: "Config", icon: Settings },
            { href: "/admin/system", label: "System Info", icon: Server },
        ],
    },
];

export function getVisibleAdminGroups(features: Features): AdminNavGroup[] {
    return adminNavigationGroups
        .map((group) => ({
            ...group,
            items: group.items.filter(
                (item) => !item.featureFlag || features[item.featureFlag],
            ),
        }))
        .filter((group) => group.items.length > 0);
}
