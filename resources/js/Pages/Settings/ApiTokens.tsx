import { Copy, Key, Plus, Trash2 } from "lucide-react";
import { toast } from "sonner";

import { useCallback, useEffect, useState } from "react";

import { Head } from "@inertiajs/react";

import PageHeader from "@/Components/layout/PageHeader";
import { Badge } from "@/Components/ui/badge";
import { Button } from "@/Components/ui/button";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/Components/ui/card";
import { ConfirmDialog } from "@/Components/ui/confirm-dialog";
import { EmptyState } from "@/Components/ui/empty-state";
import { Input } from "@/Components/ui/input";
import { Label } from "@/Components/ui/label";
import { LoadingButton } from "@/Components/ui/loading-button";
import DashboardLayout from "@/Layouts/DashboardLayout";

interface ApiToken {
  id: number;
  name: string;
  abilities: string[];
  last_used_at: string | null;
  expires_at: string | null;
  created_at: string;
}

export default function ApiTokens() {
  const [tokens, setTokens] = useState<ApiToken[]>([]);
  const [loading, setLoading] = useState(true);
  const [showCreate, setShowCreate] = useState(false);
  const [deleteTarget, setDeleteTarget] = useState<ApiToken | null>(null);

  const fetchTokens = useCallback(async () => {
    try {
      const res = await fetch("/api/tokens", {
        headers: { Accept: "application/json" },
      });
      const data = await res.json();
      setTokens(data);
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => {
    fetchTokens();
  }, [fetchTokens]);

  const handleDelete = async () => {
    if (!deleteTarget) return;
    const res = await fetch(`/api/tokens/${deleteTarget.id}`, {
      method: "DELETE",
      headers: { Accept: "application/json", "X-XSRF-TOKEN": getCsrfToken() },
    });
    setDeleteTarget(null);
    if (!res.ok) {
      toast.error("Could not revoke the token. Please try again.");
      return;
    }
    fetchTokens();
    toast.success("API token revoked.");
  };

  return (
    <DashboardLayout>
      <Head title="API Tokens" />
      <PageHeader
        title="API Tokens"
        subtitle="Manage personal access tokens for API authentication"
        actions={
          <Button onClick={() => setShowCreate(true)}>
            <Plus className="mr-2 h-4 w-4" />
            Create token
          </Button>
        }
      />
      <div className="container py-8">
        <div className="max-w-4xl mx-auto space-y-6">
          {loading ? (
            <Card>
              <CardContent className="py-12 text-center text-muted-foreground">
                Loading tokens...
              </CardContent>
            </Card>
          ) : tokens.length === 0 ? (
            <Card>
              <CardContent>
                <EmptyState
                  icon={Key}
                  title="No API tokens"
                  description="Create a token to authenticate with the API."
                  action={
                    <Button onClick={() => setShowCreate(true)}>
                      <Plus className="mr-2 h-4 w-4" />
                      Create token
                    </Button>
                  }
                />
              </CardContent>
            </Card>
          ) : (
            <Card>
              <CardHeader>
                <CardTitle>Your Tokens</CardTitle>
                <CardDescription>
                  Tokens are used to authenticate API requests. Keep them secret.
                </CardDescription>
              </CardHeader>
              <CardContent>
                <div className="space-y-3">
                  {tokens.map((token) => (
                    <div
                      key={token.id}
                      className="flex items-center justify-between rounded-md border px-4 py-3"
                    >
                      <div className="space-y-1 min-w-0 flex-1">
                        <div className="flex items-center gap-2">
                          <span className="font-medium text-sm">{token.name}</span>
                          <div className="flex gap-1">
                            {token.abilities.map((ability) => (
                              <Badge key={ability} variant="outline" className="text-xs">
                                {ability}
                              </Badge>
                            ))}
                          </div>
                        </div>
                        <div className="flex gap-3 text-xs text-muted-foreground">
                          <span>
                            Created {new Date(token.created_at).toLocaleDateString()}
                          </span>
                          {token.last_used_at && (
                            <span>
                              Last used {new Date(token.last_used_at).toLocaleDateString()}
                            </span>
                          )}
                          {token.expires_at && (
                            <span>
                              Expires {new Date(token.expires_at).toLocaleDateString()}
                            </span>
                          )}
                        </div>
                      </div>
                      <Button
                        variant="ghost"
                        size="icon"
                        className="text-destructive hover:text-destructive shrink-0"
                        onClick={() => setDeleteTarget(token)}
                        aria-label={`Revoke token ${token.name}`}
                      >
                        <Trash2 className="h-4 w-4" />
                      </Button>
                    </div>
                  ))}
                </div>
              </CardContent>
            </Card>
          )}
        </div>
      </div>

      {showCreate && (
        <CreateTokenDialog
          onClose={() => setShowCreate(false)}
          onCreated={() => {
            setShowCreate(false);
            fetchTokens();
          }}
        />
      )}

      <ConfirmDialog
        open={!!deleteTarget}
        onOpenChange={(open) => !open && setDeleteTarget(null)}
        title="Revoke API Token"
        description="This will permanently revoke this token. Any applications using it will lose access immediately."
        resourceName={deleteTarget?.name}
        resourceType="Token"
        confirmLabel="Revoke"
        variant="destructive"
        onConfirm={handleDelete}
      />
    </DashboardLayout>
  );
}

function getCsrfToken(): string {
  const match = document.cookie.match(/XSRF-TOKEN=([^;]+)/);
  return match ? decodeURIComponent(match[1]) : "";
}

const AVAILABLE_ABILITIES = [
  { value: "read", label: "Read", description: "Read-only access" },
  { value: "write", label: "Write", description: "Create and update resources" },
  { value: "delete", label: "Delete", description: "Delete resources" },
] as const;

function CreateTokenDialog({
  onClose,
  onCreated,
}: {
  onClose: () => void;
  onCreated: () => void;
}) {
  const [name, setName] = useState("");
  const [abilities, setAbilities] = useState<string[]>(["read"]);
  const [expiresAt, setExpiresAt] = useState("");
  const [creating, setCreating] = useState(false);
  const [errors, setErrors] = useState<Record<string, string[]>>({});
  const [createdToken, setCreatedToken] = useState<string | null>(null);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setCreating(true);
    setErrors({});

    const res = await fetch("/api/tokens", {
      method: "POST",
      headers: {
        Accept: "application/json",
        "Content-Type": "application/json",
        "X-XSRF-TOKEN": getCsrfToken(),
      },
      body: JSON.stringify({
        name,
        abilities: abilities.length > 0 ? abilities : undefined,
        expires_at: expiresAt || undefined,
      }),
    });

    const data = await res.json();
    setCreating(false);

    if (!res.ok) {
      if (res.status === 422 && data.errors) {
        setErrors(data.errors);
      } else {
        toast.error(data.message || "Could not create the token. Please try again.");
      }
      return;
    }

    setCreatedToken(data.token);
    toast.success("API token created.");
  };

  if (createdToken) {
    return (
      <div className="fixed inset-0 z-50 flex items-center justify-center bg-background/80 backdrop-blur-sm">
        <Card className="w-full max-w-md mx-4 max-h-[90vh] overflow-y-auto">
          <CardHeader>
            <CardTitle>Token Created</CardTitle>
            <CardDescription>
              Copy this token now. It will only be shown once.
            </CardDescription>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="flex items-center gap-2">
              <code className="flex-1 rounded-md bg-muted px-3 py-2 text-sm font-mono break-all">
                {createdToken}
              </code>
              <Button
                variant="outline"
                size="icon"
                onClick={() => {
                  navigator.clipboard.writeText(createdToken);
                  toast.success("Token copied.");
                }}
                aria-label="Copy API token"
              >
                <Copy className="h-4 w-4" />
              </Button>
            </div>
            <Button className="w-full" onClick={onCreated}>
              Done
            </Button>
          </CardContent>
        </Card>
      </div>
    );
  }

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center bg-background/80 backdrop-blur-sm">
      <Card className="w-full max-w-md mx-4 max-h-[90vh] overflow-y-auto">
        <CardHeader>
          <CardTitle>Create API Token</CardTitle>
          <CardDescription>
            Tokens authenticate your API requests. Choose permissions carefully.
          </CardDescription>
        </CardHeader>
        <CardContent>
          <form onSubmit={handleSubmit} className="space-y-4">
            <div className="space-y-2">
              <Label htmlFor="token-name">Token name</Label>
              <Input
                id="token-name"
                placeholder="e.g., Production Server"
                value={name}
                onChange={(e) => setName(e.target.value)}
                required
                aria-describedby={errors.name ? "token-name-error" : undefined}
                aria-invalid={!!errors.name}
              />
              {errors.name && (
                <p id="token-name-error" className="text-xs text-destructive">{errors.name[0]}</p>
              )}
            </div>

            <div className="space-y-2">
              <Label>Permissions</Label>
              <div className="space-y-2">
                {AVAILABLE_ABILITIES.map((ability) => (
                  <label key={ability.value} className="flex items-center gap-2 text-sm">
                    <input
                      type="checkbox"
                      checked={abilities.includes(ability.value)}
                      onChange={(e) =>
                        setAbilities(
                          e.target.checked
                            ? [...abilities, ability.value]
                            : abilities.filter((a) => a !== ability.value),
                        )
                      }
                      className="rounded"
                    />
                    <span className="font-medium">{ability.label}</span>
                    <span className="text-muted-foreground">— {ability.description}</span>
                  </label>
                ))}
              </div>
              {errors.abilities && (
                <p className="text-xs text-destructive" role="alert">{errors.abilities[0]}</p>
              )}
            </div>

            <div className="space-y-2">
              <Label htmlFor="token-expires">Expiration (optional)</Label>
              <Input
                id="token-expires"
                type="datetime-local"
                value={expiresAt}
                onChange={(e) => setExpiresAt(e.target.value)}
                min={new Date().toISOString().slice(0, 16)}
              />
              {errors.expires_at && (
                <p className="text-xs text-destructive">{errors.expires_at[0]}</p>
              )}
            </div>

            <div className="flex gap-2">
              <Button type="button" variant="outline" className="flex-1" onClick={onClose}>
                Cancel
              </Button>
              <LoadingButton type="submit" className="flex-1" loading={creating} loadingText="Creating...">
                Create token
              </LoadingButton>
            </div>
          </form>
        </CardContent>
      </Card>
    </div>
  );
}
