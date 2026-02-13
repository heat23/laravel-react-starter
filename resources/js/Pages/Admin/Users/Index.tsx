import { MoreHorizontal, Shield, Users, X } from "lucide-react";

import { useState, useCallback, useRef } from "react";

import { Head, Link, router, usePage } from "@inertiajs/react";

import { AdminDataTable } from "@/Components/admin/AdminDataTable";
import { SortHeader } from "@/Components/admin/SortHeader";
import PageHeader from "@/Components/layout/PageHeader";
import { Badge } from "@/Components/ui/badge";
import { Button } from "@/Components/ui/button";
import { Checkbox } from "@/Components/ui/checkbox";
import { ConfirmDialog } from "@/Components/ui/confirm-dialog";
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from "@/Components/ui/dropdown-menu";
import { Input } from "@/Components/ui/input";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/Components/ui/select";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/Components/ui/table";
import { useAdminAction } from "@/hooks/useAdminAction";
import { useAdminFilters } from "@/hooks/useAdminFilters";
import { useAdminKeyboardShortcuts } from "@/hooks/useAdminKeyboardShortcuts";
import { useNavigationState } from "@/hooks/useNavigationState";
import AdminLayout from "@/Layouts/AdminLayout";
import { formatRelativeTime } from "@/lib/format";
import type { PageProps } from "@/types";
import type { AdminUsersIndexProps } from "@/types/admin";

export default function AdminUsersIndex({ users, filters }: AdminUsersIndexProps) {
  const { search, setSearch, updateFilter, handleSort, handlePage, clearFilters } = useAdminFilters({
    route: "/admin/users",
    filters,
  });
  const isNavigating = useNavigationState();
  const { confirmAction, setConfirmAction, executeAction, getDialogProps } = useAdminAction();
  const currentUserId = usePage<PageProps>().props.auth.user?.id;

  const [selectedIds, setSelectedIds] = useState<Set<number>>(new Set());
  const [bulkConfirmOpen, setBulkConfirmOpen] = useState(false);
  const searchInputRef = useRef<HTMLInputElement>(null);

  const currentPage = users.current_page;
  const lastPage = users.last_page;
  useAdminKeyboardShortcuts({
    onSearch: () => searchInputRef.current?.focus(),
    onNextPage: currentPage < lastPage ? () => handlePage(currentPage + 1) : undefined,
    onPrevPage: currentPage > 1 ? () => handlePage(currentPage - 1) : undefined,
  });

  // Only non-admin, active, non-self users are eligible for bulk deactivation
  const selectableIds = new Set(
    users.data.filter((u) => !u.is_admin && !u.deleted_at && u.id !== currentUserId).map((u) => u.id),
  );
  const allSelectableSelected = selectableIds.size > 0 && [...selectableIds].every((id) => selectedIds.has(id));

  const toggleUser = useCallback((id: number) => {
    setSelectedIds((prev) => {
      const next = new Set(prev);
      if (next.has(id)) next.delete(id);
      else next.add(id);
      return next;
    });
  }, []);

  const toggleAll = useCallback(() => {
    if (allSelectableSelected) {
      setSelectedIds(new Set());
    } else {
      setSelectedIds(new Set(selectableIds));
    }
  }, [allSelectableSelected, selectableIds]);

  const executeBulkDeactivate = useCallback((): Promise<void> => {
    return new Promise((resolve, reject) => {
      const ids = Array.from(selectedIds);
      if (ids.length === 0) {
        resolve();
        return;
      }
      router.post("/admin/users/bulk-deactivate", { ids }, {
        preserveState: true,
        onSuccess: () => {
          setSelectedIds(new Set());
          setBulkConfirmOpen(false);
          resolve();
        },
        onError: () => reject(),
      });
    });
  }, [selectedIds]);

  return (
    <AdminLayout>
      <Head title="Admin - Users" />
      <PageHeader title="Users" subtitle="Manage user accounts" />

      <div className="container py-8 space-y-4">
        {/* Filters */}
        <fieldset className="flex flex-col sm:flex-row gap-3">
          <legend className="sr-only">User Filters</legend>
          <Input
            ref={searchInputRef}
            placeholder="Search by name or email..."
            value={search}
            onChange={(e) => setSearch(e.target.value)}
            className="max-w-sm"
            aria-label="Search users by name or email"
          />
          <Select value={filters.admin ?? "all"} onValueChange={(value) => updateFilter({ admin: value === "all" ? undefined : value })}>
            <SelectTrigger className="w-[180px]" aria-label="Filter by admin status">
              <SelectValue placeholder="All users" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="all">All Users</SelectItem>
              <SelectItem value="1">Admins Only</SelectItem>
              <SelectItem value="0">Non-Admins</SelectItem>
            </SelectContent>
          </Select>
        </fieldset>

        {/* Bulk Action Bar */}
        {selectedIds.size > 0 && (
          <div className="flex items-center gap-3 rounded-lg border bg-muted/50 px-4 py-2" role="status" aria-live="polite">
            <span className="text-sm font-medium">{selectedIds.size} user(s) selected</span>
            <Button
              variant="destructive"
              size="sm"
              onClick={() => setBulkConfirmOpen(true)}
            >
              Deactivate Selected
            </Button>
            <Button
              variant="ghost"
              size="sm"
              onClick={() => setSelectedIds(new Set())}
              aria-label="Clear selection"
            >
              <X className="mr-1 h-4 w-4" />
              Clear
            </Button>
          </div>
        )}

        <AdminDataTable
          isEmpty={users.data.length === 0}
          isNavigating={isNavigating}
          pagination={users}
          onPage={handlePage}
          paginationLabel="users"
          emptyIcon={Users}
          emptyTitle="No users found"
          emptyDescription={
            filters.search || filters.admin
              ? "No users match your current filters. Try adjusting your search or filter."
              : "No users in the system yet."
          }
          emptyAction={
            (filters.search || filters.admin) ? (
              <Button variant="outline" size="sm" onClick={clearFilters}>
                Clear filters
              </Button>
            ) : undefined
          }
        >
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead className="w-[40px]">
                      <Checkbox
                        checked={allSelectableSelected && selectableIds.size > 0}
                        onCheckedChange={toggleAll}
                        aria-label="Select all users"
                        disabled={selectableIds.size === 0}
                      />
                    </TableHead>
                    <SortHeader column="name" label="Name" currentSort={filters.sort} currentDir={filters.dir} onSort={handleSort} />
                    <SortHeader column="email" label="Email" currentSort={filters.sort} currentDir={filters.dir} onSort={handleSort} />
                    <TableHead>Admin</TableHead>
                    <TableHead>Verified</TableHead>
                    <SortHeader column="last_login_at" label="Last Login" currentSort={filters.sort} currentDir={filters.dir} onSort={handleSort} />
                    <SortHeader column="created_at" label="Created" currentSort={filters.sort} currentDir={filters.dir} onSort={handleSort} />
                    <TableHead>Status</TableHead>
                    <TableHead className="w-[50px]" />
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {users.data.map((user) => {
                    const isSelectable = selectableIds.has(user.id);
                    return (
                    <TableRow key={user.id} data-selected={selectedIds.has(user.id) || undefined}>
                      <TableCell>
                        {isSelectable ? (
                          <Checkbox
                            checked={selectedIds.has(user.id)}
                            onCheckedChange={() => toggleUser(user.id)}
                            aria-label={`Select ${user.name}`}
                          />
                        ) : (
                          <Checkbox disabled aria-label={`Cannot select ${user.name}`} />
                        )}
                      </TableCell>
                      <TableCell>
                        <Link href={`/admin/users/${user.id}`} className="font-medium hover:underline">
                          {user.name}
                        </Link>
                      </TableCell>
                      <TableCell className="text-sm text-muted-foreground max-w-50 truncate" title={user.email}>{user.email}</TableCell>
                      <TableCell>
                        {user.is_admin ? (
                          <Badge variant="success">Admin</Badge>
                        ) : (
                          <Badge variant="secondary">User</Badge>
                        )}
                      </TableCell>
                      <TableCell>
                        {user.email_verified_at ? (
                          <Badge variant="secondary">Verified</Badge>
                        ) : (
                          <Badge variant="outline" className="text-muted-foreground">Unverified</Badge>
                        )}
                      </TableCell>
                      <TableCell className="text-sm text-muted-foreground">
                        {formatRelativeTime(user.last_login_at)}
                      </TableCell>
                      <TableCell className="text-sm text-muted-foreground">
                        {formatRelativeTime(user.created_at)}
                      </TableCell>
                      <TableCell>
                        {user.deleted_at ? (
                          <Badge variant="destructive">Deactivated</Badge>
                        ) : (
                          <Badge variant="success">Active</Badge>
                        )}
                      </TableCell>
                      <TableCell>
                        <DropdownMenu>
                          <DropdownMenuTrigger asChild>
                            <Button variant="ghost" size="icon" className="h-8 w-8" aria-label="User actions">
                              <MoreHorizontal className="h-4 w-4" />
                            </Button>
                          </DropdownMenuTrigger>
                          <DropdownMenuContent align="end">
                            <DropdownMenuItem asChild>
                              <Link href={`/admin/users/${user.id}`}>View Details</Link>
                            </DropdownMenuItem>
                            <DropdownMenuSeparator />
                            <DropdownMenuItem onClick={() => setConfirmAction({ type: "toggleAdmin", user })}>
                              <Shield className="mr-2 h-4 w-4" />
                              {user.is_admin ? "Remove Admin" : "Make Admin"}
                            </DropdownMenuItem>
                            <DropdownMenuItem onClick={() => setConfirmAction({ type: "toggleActive", user })}>
                              {user.deleted_at ? "Restore User" : "Deactivate User"}
                            </DropdownMenuItem>
                            {!user.is_admin && !user.deleted_at && user.id !== currentUserId && (
                              <DropdownMenuItem onClick={() => setConfirmAction({ type: "impersonate", user })}>
                                Impersonate
                              </DropdownMenuItem>
                            )}
                          </DropdownMenuContent>
                        </DropdownMenu>
                      </TableCell>
                    </TableRow>
                    );
                  })}
                </TableBody>
              </Table>
        </AdminDataTable>
      </div>

      {/* Single Action Confirm Dialog */}
      <ConfirmDialog
        open={!!confirmAction}
        onOpenChange={(open) => !open && setConfirmAction(null)}
        onConfirm={executeAction}
        {...getDialogProps()}
      />

      {/* Bulk Deactivate Confirm Dialog */}
      <ConfirmDialog
        open={bulkConfirmOpen}
        onOpenChange={setBulkConfirmOpen}
        onConfirm={executeBulkDeactivate}
        title="Bulk Deactivate Users"
        description={`Are you sure you want to deactivate ${selectedIds.size} user(s)? They will not be able to log in.`}
        variant="destructive"
        confirmLabel="Deactivate"
      />
    </AdminLayout>
  );
}
