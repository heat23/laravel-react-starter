import { AlertTriangle, ChevronDown, ChevronRight, Lock, RefreshCw, Search, Trash2, Users, X } from "lucide-react";
import { toast } from "sonner";

import { Fragment, FormEvent, useState } from "react";

import { Head, router } from "@inertiajs/react";

import PageHeader from "@/Components/layout/PageHeader";
import { Badge } from "@/Components/ui/badge";
import { Button } from "@/Components/ui/button";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/Components/ui/card";
import { ConfirmDialog } from "@/Components/ui/confirm-dialog";
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from "@/Components/ui/dropdown-menu";
import { Input } from "@/Components/ui/input";
import { Switch } from "@/Components/ui/switch";
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/Components/ui/table";
import AdminLayout from "@/Layouts/AdminLayout";
import type {
  AdminFeatureFlagsIndexProps,
  FeatureFlagAdmin,
  FeatureFlagUserOverride,
  FeatureFlagUserSearch,
} from "@/types/admin";

export default function FeatureFlagsIndex({ flags }: AdminFeatureFlagsIndexProps) {
  const [expandedFlag, setExpandedFlag] = useState<string | null>(null);
  const [userOverrides, setUserOverrides] = useState<Record<string, FeatureFlagUserOverride[]>>({});
  const [loadingUsers, setLoadingUsers] = useState<string | null>(null);
  const [searchQuery, setSearchQuery] = useState("");
  const [searchResults, setSearchResults] = useState<FeatureFlagUserSearch[]>([]);
  const [searching, setSearching] = useState(false);
  const [confirmDialog, setConfirmDialog] = useState<{
    open: boolean;
    title: string;
    description: string;
    onConfirm: () => Promise<void>;
    variant?: "default" | "destructive";
  }>({ open: false, title: "", description: "", onConfirm: async () => {} });

  const handleToggleGlobal = async (flag: FeatureFlagAdmin, newValue: boolean) => {
    return new Promise<void>((resolve, reject) => {
      router.patch(
        `/admin/feature-flags/${flag.flag}`,
        { enabled: newValue },
        {
          preserveState: true,
          onSuccess: () => {
            toast.success(`${flag.flag} is now ${newValue ? "enabled" : "disabled"} globally.`);
            resolve();
          },
          onError: (errors) => {
            toast.error(Object.values(errors)[0] as string || "Failed to update feature flag.");
            reject(new Error("Failed to update"));
          },
        }
      );
    });
  };

  const handleResetToDefault = async (flag: FeatureFlagAdmin) => {
    return new Promise<void>((resolve, reject) => {
      router.delete(`/admin/feature-flags/${flag.flag}`, {
        preserveState: true,
        onSuccess: () => {
          toast.success(`${flag.flag} reset to environment default.`);
          resolve();
        },
        onError: () => {
          toast.error("Failed to reset feature flag.");
          reject(new Error("Failed to reset"));
        },
      });
    });
  };

  const loadUserOverrides = async (flag: string) => {
    setLoadingUsers(flag);
    try {
      const response = await fetch(`/admin/feature-flags/${flag}/users`);
      const data = await response.json();
      setUserOverrides((prev) => ({ ...prev, [flag]: data }));
    } catch {
      toast.error("Failed to load user overrides.");
    } finally {
      setLoadingUsers(null);
    }
  };

  const handleExpandFlag = (flag: string) => {
    if (expandedFlag === flag) {
      setExpandedFlag(null);
      // Clear stale search data when collapsing
      setSearchQuery("");
      setSearchResults([]);
    } else {
      setExpandedFlag(flag);
      if (!userOverrides[flag]) {
        loadUserOverrides(flag);
      }
    }
  };

  const handleSearchUsers = async (e: FormEvent) => {
    e.preventDefault();
    if (searchQuery.length < 2) return;

    setSearching(true);
    try {
      const response = await fetch(`/admin/feature-flags/search-users?q=${encodeURIComponent(searchQuery)}`);
      const data = await response.json();
      setSearchResults(Array.isArray(data) ? data : []);
    } catch {
      toast.error("Failed to search users.");
    } finally {
      setSearching(false);
    }
  };

  const handleAddUserOverride = async (flag: string, userId: number, enabled: boolean) => {
    return new Promise<void>((resolve, reject) => {
      router.post(
        `/admin/feature-flags/${flag}/users`,
        { user_id: userId, enabled },
        {
          preserveState: true,
          onSuccess: () => {
            toast.success("User override added successfully.");
            loadUserOverrides(flag);
            setSearchQuery("");
            setSearchResults([]);
            resolve();
          },
          onError: () => {
            toast.error("Failed to add user override.");
            reject(new Error("Failed to add"));
          },
        }
      );
    });
  };

  const handleRemoveUserOverride = async (flag: string, userId: number) => {
    return new Promise<void>((resolve, reject) => {
      router.delete(`/admin/feature-flags/${flag}/users/${userId}`, {
        preserveState: true,
        onSuccess: () => {
          toast.success("User override removed successfully.");
          loadUserOverrides(flag);
          resolve();
        },
        onError: () => {
          toast.error("Failed to remove user override.");
          reject(new Error("Failed to remove"));
        },
      });
    });
  };

  const handleRemoveAllUserOverrides = async (flag: string) => {
    return new Promise<void>((resolve, reject) => {
      router.delete(`/admin/feature-flags/${flag}/users`, {
        preserveState: true,
        onSuccess: () => {
          toast.success("All user overrides removed.");
          loadUserOverrides(flag);
          resolve();
        },
        onError: () => {
          toast.error("Failed to remove user overrides.");
          reject(new Error("Failed to remove"));
        },
      });
    });
  };

  const openConfirmDialog = (config: Omit<typeof confirmDialog, "open">) => {
    setConfirmDialog({ ...config, open: true });
  };

  return (
    <AdminLayout>
      <Head title="Admin - Feature Flags" />
      <PageHeader
        title="Feature Flags"
        subtitle="Manage feature toggles and per-user targeting"
      />

      <div className="container py-8">
        <Card>
          <CardHeader>
            <CardTitle>Feature Flags</CardTitle>
            <CardDescription>
              Override feature flags globally or target specific users. Protected flags cannot be modified.
            </CardDescription>
          </CardHeader>
          <CardContent>
            <div className="overflow-x-auto">
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead className="w-8"></TableHead>
                    <TableHead>Flag</TableHead>
                    <TableHead>Env Default</TableHead>
                    <TableHead>Global Override</TableHead>
                    <TableHead>Effective</TableHead>
                    <TableHead>User Overrides</TableHead>
                    <TableHead className="text-right">Actions</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {flags.map((flag) => (
                    <Fragment key={flag.flag}>
                      <TableRow>
                        <TableCell>
                          {flag.user_override_count > 0 ? (
                            <button
                              onClick={() => handleExpandFlag(flag.flag)}
                              aria-label={expandedFlag === flag.flag
                                ? `Collapse user overrides for ${flag.flag}`
                                : `Expand user overrides for ${flag.flag}`}
                              aria-expanded={expandedFlag === flag.flag}
                              aria-controls={`overrides-${flag.flag}`}
                              className="p-1 hover:bg-muted rounded"
                            >
                              {expandedFlag === flag.flag ? (
                                <ChevronDown className="h-4 w-4" />
                              ) : (
                                <ChevronRight className="h-4 w-4" />
                              )}
                            </button>
                          ) : (
                            <button
                              onClick={() => handleExpandFlag(flag.flag)}
                              aria-label={`No user overrides available for ${flag.flag}`}
                              className="p-1 hover:bg-muted rounded opacity-50"
                            >
                              <ChevronRight className="h-4 w-4" />
                            </button>
                          )}
                        </TableCell>
                        <TableCell>
                          <div className="flex items-center gap-2">
                            <code className="font-mono text-sm">{flag.flag}</code>
                            {flag.is_protected && (
                              <Badge variant="outline" className="gap-1">
                                <Lock className="h-3 w-3" />
                                Protected
                              </Badge>
                            )}
                            {flag.is_route_dependent && !flag.env_default && flag.global_override === true && (
                              <Badge variant="destructive" className="gap-1">
                                <AlertTriangle className="h-3 w-3" />
                                Route unavailable
                              </Badge>
                            )}
                          </div>
                        </TableCell>
                        <TableCell>
                          <Badge variant={flag.env_default ? "success" : "secondary"}>
                            {flag.env_default ? "ON" : "OFF"}
                          </Badge>
                        </TableCell>
                        <TableCell>
                          {flag.global_override === null ? (
                            <Badge variant="outline" className="text-muted-foreground">
                              —
                            </Badge>
                          ) : (
                            <Badge variant={flag.global_override ? "success" : "destructive"}>
                              {flag.global_override ? "ON" : "OFF"}
                            </Badge>
                          )}
                        </TableCell>
                        <TableCell>
                          <Badge variant={flag.effective ? "success" : "secondary"}>
                            {flag.effective ? "ON" : "OFF"}
                          </Badge>
                        </TableCell>
                        <TableCell>
                          {flag.user_override_count > 0 ? (
                            <button
                              onClick={() => handleExpandFlag(flag.flag)}
                              className="flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground"
                            >
                              <Users className="h-4 w-4" />
                              {flag.user_override_count} user{flag.user_override_count !== 1 ? "s" : ""}
                            </button>
                          ) : (
                            <span className="text-sm text-muted-foreground">—</span>
                          )}
                        </TableCell>
                        <TableCell className="text-right">
                          <div className="flex items-center justify-end gap-2">
                            <Switch
                              checked={flag.global_override ?? flag.env_default}
                              disabled={flag.is_protected}
                              onCheckedChange={(checked) => {
                                openConfirmDialog({
                                  title: checked ? "Enable feature" : "Disable feature",
                                  description: `Are you sure you want to ${checked ? "enable" : "disable"} "${flag.flag}" globally?`,
                                  variant: "default",
                                  onConfirm: () => handleToggleGlobal(flag, checked),
                                });
                              }}
                            />
                            <DropdownMenu>
                              <DropdownMenuTrigger asChild>
                                <Button
                                  variant="ghost"
                                  size="sm"
                                  disabled={flag.is_protected}
                                  aria-label={`Options for ${flag.flag} feature flag`}
                                >
                                  <ChevronDown className="h-4 w-4" />
                                </Button>
                              </DropdownMenuTrigger>
                              <DropdownMenuContent align="end">
                                <DropdownMenuItem
                                  onClick={() => {
                                    openConfirmDialog({
                                      title: "Reset to default",
                                      description: `Reset "${flag.flag}" to its environment default value?`,
                                      variant: "default",
                                      onConfirm: () => handleResetToDefault(flag),
                                    });
                                  }}
                                  disabled={flag.global_override === null}
                                >
                                  <RefreshCw className="mr-2 h-4 w-4" />
                                  Reset to env default
                                </DropdownMenuItem>
                                <DropdownMenuItem onClick={() => handleExpandFlag(flag.flag)}>
                                  <Users className="mr-2 h-4 w-4" />
                                  Manage user targeting
                                </DropdownMenuItem>
                              </DropdownMenuContent>
                            </DropdownMenu>
                          </div>
                        </TableCell>
                      </TableRow>
                      {expandedFlag === flag.flag && (
                        <TableRow
                          key={`${flag.flag}-expanded`}
                          id={`overrides-${flag.flag}`}
                          className="animate-in fade-in duration-200"
                        >
                          <TableCell colSpan={7} className="bg-muted/50 p-4">
                            <div className="space-y-4">
                              <div className="flex items-center justify-between">
                                <h4 className="font-medium">User-Specific Overrides</h4>
                                {(userOverrides[flag.flag]?.length ?? 0) > 0 && !flag.is_protected && (
                                  <Button
                                    variant="ghost"
                                    size="sm"
                                    onClick={() => {
                                      openConfirmDialog({
                                        title: "Remove all user overrides",
                                        description: `Remove all ${userOverrides[flag.flag]?.length ?? 0} user overrides for "${flag.flag}"?`,
                                        variant: "destructive",
                                        onConfirm: () => handleRemoveAllUserOverrides(flag.flag),
                                      });
                                    }}
                                  >
                                    <Trash2 className="mr-2 h-4 w-4" />
                                    Clear all
                                  </Button>
                                )}
                              </div>

                              {!flag.is_protected && (
                                <form onSubmit={handleSearchUsers} className="flex gap-2">
                                  <div className="relative flex-1">
                                    <label htmlFor={`user-search-${flag.flag}`} className="sr-only">
                                      Search users by name or email for {flag.flag}
                                    </label>
                                    <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                                    <Input
                                      id={`user-search-${flag.flag}`}
                                      placeholder="Search users by name or email..."
                                      value={searchQuery}
                                      onChange={(e) => setSearchQuery(e.target.value)}
                                      className="pl-9"
                                    />
                                  </div>
                                  <Button type="submit" disabled={searchQuery.length < 2 || searching}>
                                    {searching ? "Searching..." : "Search"}
                                  </Button>
                                </form>
                              )}

                              {searchResults.length > 0 && (
                                <div className="rounded-md border">
                                  <Table>
                                    <TableHeader>
                                      <TableRow>
                                        <TableHead>User</TableHead>
                                        <TableHead className="text-right">Add Override</TableHead>
                                      </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                      {searchResults.map((user) => (
                                        <TableRow key={user.id}>
                                          <TableCell>
                                            <div>
                                              <div className="font-medium">{user.name}</div>
                                              <div className="text-sm text-muted-foreground">{user.email}</div>
                                            </div>
                                          </TableCell>
                                          <TableCell className="text-right">
                                            <div className="flex justify-end gap-2">
                                              <Button
                                                size="sm"
                                                variant="outline"
                                                onClick={() => {
                                                  openConfirmDialog({
                                                    title: "Enable for user",
                                                    description: `Enable "${flag.flag}" for ${user.name}?`,
                                                    onConfirm: () => handleAddUserOverride(flag.flag, user.id, true),
                                                  });
                                                }}
                                              >
                                                Enable
                                              </Button>
                                              <Button
                                                size="sm"
                                                variant="outline"
                                                onClick={() => {
                                                  openConfirmDialog({
                                                    title: "Disable for user",
                                                    description: `Disable "${flag.flag}" for ${user.name}?`,
                                                    onConfirm: () => handleAddUserOverride(flag.flag, user.id, false),
                                                  });
                                                }}
                                              >
                                                Disable
                                              </Button>
                                            </div>
                                          </TableCell>
                                        </TableRow>
                                      ))}
                                    </TableBody>
                                  </Table>
                                </div>
                              )}

                              {loadingUsers === flag.flag ? (
                                <div
                                  className="text-sm text-muted-foreground"
                                  role="status"
                                  aria-live="polite"
                                >
                                  Loading user overrides...
                                </div>
                              ) : userOverrides[flag.flag]?.length ? (
                                <div className="rounded-md border">
                                  <Table>
                                    <TableHeader>
                                      <TableRow>
                                        <TableHead>User</TableHead>
                                        <TableHead>Override</TableHead>
                                        <TableHead className="text-right">Actions</TableHead>
                                      </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                      {userOverrides[flag.flag].map((override) => (
                                        <TableRow key={override.user_id}>
                                          <TableCell>
                                            <div>
                                              <div className="font-medium">{override.name}</div>
                                              <div className="text-sm text-muted-foreground">{override.email}</div>
                                            </div>
                                          </TableCell>
                                          <TableCell>
                                            <Badge variant={override.enabled ? "success" : "destructive"}>
                                              {override.enabled ? "ON" : "OFF"}
                                            </Badge>
                                          </TableCell>
                                          <TableCell className="text-right">
                                            <Button
                                              variant="ghost"
                                              size="sm"
                                              aria-label={`Remove override for ${override.name}`}
                                              onClick={() => {
                                                openConfirmDialog({
                                                  title: "Remove override",
                                                  description: `Remove override for ${override.name}?`,
                                                  variant: "destructive",
                                                  onConfirm: () => handleRemoveUserOverride(flag.flag, override.user_id),
                                                });
                                              }}
                                              disabled={flag.is_protected}
                                            >
                                              <X className="h-4 w-4" />
                                            </Button>
                                          </TableCell>
                                        </TableRow>
                                      ))}
                                    </TableBody>
                                  </Table>
                                </div>
                              ) : (
                                <div className="text-sm text-muted-foreground">
                                  No user-specific overrides. Search for users above to add targeting.
                                </div>
                              )}
                            </div>
                          </TableCell>
                        </TableRow>
                      )}
                    </Fragment>
                  ))}
                </TableBody>
              </Table>
            </div>
          </CardContent>
        </Card>
      </div>

      <ConfirmDialog
        open={confirmDialog.open}
        onOpenChange={(open) => setConfirmDialog((prev) => ({ ...prev, open }))}
        title={confirmDialog.title}
        description={confirmDialog.description}
        variant={confirmDialog.variant}
        onConfirm={confirmDialog.onConfirm}
      />
    </AdminLayout>
  );
}
