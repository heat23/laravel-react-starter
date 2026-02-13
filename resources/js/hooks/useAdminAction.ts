import { useState } from "react";

import { router } from "@inertiajs/react";

export type AdminActionType = "toggleAdmin" | "toggleActive" | "impersonate";

export interface AdminActionTarget {
  id: number;
  name: string;
  is_admin: boolean;
  deleted_at: string | null;
}

interface ConfirmState {
  type: AdminActionType;
  user: AdminActionTarget;
}

interface DialogProps {
  title: string;
  description: string;
  variant: "destructive" | "default";
  confirmLabel: string;
}

export function useAdminAction() {
  const [confirmAction, setConfirmAction] = useState<ConfirmState | null>(null);

  function executeAction(): Promise<void> {
    return new Promise((resolve, reject) => {
      if (!confirmAction) {
        resolve();
        return;
      }
      const { type, user } = confirmAction;
      const options = { preserveState: true, onSuccess: () => resolve(), onError: () => reject() };

      if (type === "toggleAdmin") {
        router.patch(`/admin/users/${user.id}/toggle-admin`, {}, options);
      } else if (type === "toggleActive") {
        router.patch(`/admin/users/${user.id}/toggle-active`, {}, options);
      } else if (type === "impersonate") {
        router.post(`/admin/users/${user.id}/impersonate`, {}, { onSuccess: () => resolve(), onError: () => reject() });
      } else {
        resolve();
      }
    });
  }

  function getDialogProps(): DialogProps {
    if (!confirmAction) {
      return { title: "", description: "", variant: "default", confirmLabel: "Confirm" };
    }

    const { type, user } = confirmAction;

    if (type === "toggleAdmin") {
      return {
        title: user.is_admin ? "Remove Admin Access" : "Grant Admin Access",
        description: `Are you sure you want to ${user.is_admin ? "remove admin access from" : "grant admin access to"} ${user.name}?`,
        variant: user.is_admin ? "destructive" : "default",
        confirmLabel: "Confirm",
      };
    }

    if (type === "toggleActive") {
      return {
        title: user.deleted_at ? "Restore User" : "Deactivate User",
        description: user.deleted_at
          ? `Restore ${user.name}? They will be able to log in again.`
          : `Deactivate ${user.name}? They will not be able to log in.`,
        variant: user.deleted_at ? "default" : "destructive",
        confirmLabel: "Confirm",
      };
    }

    return {
      title: "Impersonate User",
      description: `You will be logged in as ${user.name}. You can end impersonation from the top banner.`,
      variant: "default",
      confirmLabel: "Confirm",
    };
  }

  return { confirmAction, setConfirmAction, executeAction, getDialogProps };
}
