import { CreditCard, Home, Key, Radio, Shield, User } from "lucide-react";
import type { LucideIcon } from "lucide-react";

import type { Features } from "@/types";

export interface NavItem {
  href: string;
  label: string;
  icon: LucideIcon;
  shortcut?: string;
  featureFlag?: keyof Features;
}

export interface NavGroup {
  label: string;
  items: NavItem[];
}

export const navigationGroups: NavGroup[] = [
  {
    label: "Main",
    items: [
      { href: "/dashboard", label: "Dashboard", icon: Home, shortcut: "G D" },
      { href: "/profile", label: "Profile", icon: User, shortcut: "G P" },
    ],
  },
  {
    label: "Settings",
    items: [
      {
        href: "/settings/tokens",
        label: "API Tokens",
        icon: Key,
        shortcut: "G T",
        featureFlag: "apiTokens",
      },
      {
        href: "/settings/security",
        label: "Security",
        icon: Shield,
        shortcut: "G S",
        featureFlag: "twoFactor",
      },
      {
        href: "/settings/webhooks",
        label: "Webhooks",
        icon: Radio,
        shortcut: "G W",
        featureFlag: "webhooks",
      },
      {
        href: "/billing",
        label: "Billing",
        icon: CreditCard,
        shortcut: "G B",
        featureFlag: "billing",
      },
    ],
  },
];

export function getVisibleNavItems(features: Record<string, boolean>): NavItem[] {
  return navigationGroups
    .flatMap((g) => g.items)
    .filter((item) => !item.featureFlag || features[item.featureFlag]);
}

export function getVisibleGroups(features: Record<string, boolean>): NavGroup[] {
  return navigationGroups
    .map((group) => ({
      ...group,
      items: group.items.filter(
        (item) => !item.featureFlag || features[item.featureFlag],
      ),
    }))
    .filter((group) => group.items.length > 0);
}
