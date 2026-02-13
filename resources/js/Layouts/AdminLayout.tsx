import { ArrowLeft, Shield } from "lucide-react";

import { PropsWithChildren } from "react";

import { Link, usePage } from "@inertiajs/react";

import SidebarLayout from "@/Components/sidebar/sidebar-layout";
import { Badge } from "@/Components/ui/badge";
import { Button } from "@/Components/ui/button";
import { getVisibleAdminGroups } from "@/config/admin-navigation";
import type { PageProps } from "@/types";

export default function AdminLayout({ children }: PropsWithChildren) {
  const { features } = usePage<PageProps>().props;
  const visibleGroups = getVisibleAdminGroups(features);

  return (
    <SidebarLayout
      navigationGroups={visibleGroups}
      logoHref="/admin"
      headerExtra={
        <Badge variant="outline" className="border-destructive/50 text-destructive text-xs">
          <Shield className="h-3 w-3 mr-1" />
          Admin
        </Badge>
      }
      footerExtra={
        <Button variant="ghost" size="sm" className="w-full justify-start" asChild>
          <Link href="/dashboard">
            <ArrowLeft className="mr-2 h-4 w-4" />
            Back to App
          </Link>
        </Button>
      }
    >
      {children}
    </SidebarLayout>
  );
}
