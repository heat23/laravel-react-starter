import { Copy, Eye, EyeOff, Plus, Radio, RefreshCw, Send, Trash2 } from "lucide-react";
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
import type { WebhookDelivery, WebhookEndpoint } from "@/types";

interface WebhooksProps {
  available_events: string[];
}

export default function Webhooks({ available_events }: WebhooksProps) {
  const [endpoints, setEndpoints] = useState<WebhookEndpoint[]>([]);
  const [loading, setLoading] = useState(true);
  const [showCreate, setShowCreate] = useState(false);
  const [selectedEndpoint, setSelectedEndpoint] = useState<WebhookEndpoint | null>(null);
  const [showSecret, setShowSecret] = useState<number | null>(null);
  const [deliveries, setDeliveries] = useState<WebhookDelivery[]>([]);
  const [deleteTarget, setDeleteTarget] = useState<WebhookEndpoint | null>(null);

  const fetchEndpoints = useCallback(async () => {
    try {
      const res = await fetch("/api/webhooks", {
        headers: { Accept: "application/json" },
      });
      const data = await res.json();
      setEndpoints(data);
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => {
    fetchEndpoints();
  }, [fetchEndpoints]);

  const handleDelete = async () => {
    if (!deleteTarget) return;
    const res = await fetch(`/api/webhooks/${deleteTarget.id}`, {
      method: "DELETE",
      headers: { Accept: "application/json", "X-XSRF-TOKEN": getCsrfToken() },
    });
    setDeleteTarget(null);
    if (!res.ok) {
      toast.error("Could not delete the endpoint. Please try again or check your connection.");
      return;
    }
    fetchEndpoints();
    toast.success("Webhook endpoint deleted.");
  };

  const handleTest = async (id: number) => {
    const res = await fetch(`/api/webhooks/${id}/test`, {
      method: "POST",
      headers: { Accept: "application/json", "X-XSRF-TOKEN": getCsrfToken() },
    });
    if (res.ok) {
      toast.success("Test webhook queued.");
    } else {
      toast.error("Could not send the test webhook. Please verify the endpoint is active and try again.");
    }
  };

  const loadDeliveries = async (endpoint: WebhookEndpoint) => {
    setSelectedEndpoint(endpoint);
    const res = await fetch(`/api/webhooks/${endpoint.id}/deliveries`, {
      headers: { Accept: "application/json" },
    });
    const data = await res.json();
    setDeliveries(data);
  };

  return (
    <DashboardLayout>
      <Head title="Webhooks" />
      <PageHeader
        title="Webhooks"
        subtitle="Manage webhook endpoints for event notifications"
        actions={
          <Button onClick={() => setShowCreate(true)}>
            <Plus className="mr-2 h-4 w-4" />
            Add endpoint
          </Button>
        }
      />
      <div className="container py-8">
        <div className="max-w-4xl mx-auto space-y-6">
          {loading ? (
            <Card>
              <CardContent className="py-12 text-center text-muted-foreground">
                Loading endpoints...
              </CardContent>
            </Card>
          ) : endpoints.length === 0 ? (
            <Card>
              <CardContent>
                <EmptyState
                  icon={Radio}
                  title="No webhook endpoints"
                  description="Create your first endpoint to start receiving event notifications."
                  action={
                    <Button onClick={() => setShowCreate(true)}>
                      <Plus className="mr-2 h-4 w-4" />
                      Add endpoint
                    </Button>
                  }
                />
              </CardContent>
            </Card>
          ) : (
            endpoints.map((endpoint) => (
              <Card key={endpoint.id}>
                <CardHeader>
                  <div className="flex items-center justify-between">
                    <div className="space-y-1 min-w-0 flex-1">
                      <CardTitle className="text-base font-mono truncate">
                        {endpoint.url}
                      </CardTitle>
                      {endpoint.description && (
                        <CardDescription>{endpoint.description}</CardDescription>
                      )}
                    </div>
                    <Badge variant={endpoint.active ? "default" : "secondary"}>
                      {endpoint.active ? "Active" : "Inactive"}
                    </Badge>
                  </div>
                </CardHeader>
                <CardContent>
                  <div className="space-y-3">
                    <div className="flex flex-wrap gap-1.5">
                      {endpoint.events.map((event) => (
                        <Badge key={event} variant="outline" className="text-xs">
                          {event}
                        </Badge>
                      ))}
                    </div>

                    {showSecret === endpoint.id && endpoint.secret && (
                      <div className="flex items-center gap-2">
                        <code className="flex-1 rounded-md bg-muted px-3 py-2 text-xs font-mono break-all">
                          {endpoint.secret}
                        </code>
                        <Button
                          variant="outline"
                          size="icon"
                          className="shrink-0"
                          onClick={() => {
                            navigator.clipboard.writeText(endpoint.secret!);
                            toast.success("Secret copied.");
                          }}
                          aria-label="Copy webhook secret"
                        >
                          <Copy className="h-3 w-3" />
                        </Button>
                      </div>
                    )}

                    <div className="flex flex-wrap gap-2">
                      <Button
                        variant="outline"
                        size="sm"
                        onClick={() => setShowSecret(showSecret === endpoint.id ? null : endpoint.id)}
                      >
                        {showSecret === endpoint.id ? (
                          <><EyeOff className="mr-1.5 h-3 w-3" /> Hide secret</>
                        ) : (
                          <><Eye className="mr-1.5 h-3 w-3" /> Show secret</>
                        )}
                      </Button>
                      <Button variant="outline" size="sm" onClick={() => loadDeliveries(endpoint)}>
                        <RefreshCw className="mr-1.5 h-3 w-3" /> Deliveries
                      </Button>
                      <Button variant="outline" size="sm" onClick={() => handleTest(endpoint.id)}>
                        Test
                      </Button>
                      <Button
                        variant="destructive"
                        size="sm"
                        onClick={() => setDeleteTarget(endpoint)}
                      >
                        <Trash2 className="mr-1.5 h-3 w-3" /> Delete
                      </Button>
                    </div>
                  </div>
                </CardContent>
              </Card>
            ))
          )}

          {selectedEndpoint && (
            <Card>
              <CardHeader>
                <CardTitle className="text-base">
                  Recent Deliveries - {selectedEndpoint.url}
                </CardTitle>
              </CardHeader>
              <CardContent>
                {deliveries.length === 0 ? (
                  <EmptyState
                    icon={Send}
                    title="No deliveries yet"
                    description="Deliveries will appear here when events are sent to this endpoint."
                    size="sm"
                  />
                ) : (
                <div className="space-y-2">
                  {deliveries.map((delivery) => (
                    <div
                      key={delivery.id}
                      className="flex items-center justify-between rounded-md border px-3 py-2 text-sm"
                    >
                      <div className="flex items-center gap-3">
                        <Badge
                          variant={
                            delivery.status === "success"
                              ? "default"
                              : delivery.status === "failed"
                                ? "destructive"
                                : "secondary"
                          }
                          className="text-xs"
                        >
                          {delivery.status}
                        </Badge>
                        <span className="font-mono text-xs">{delivery.event_type}</span>
                      </div>
                      <div className="flex items-center gap-3 text-muted-foreground text-xs">
                        {delivery.response_code && (
                          <span>HTTP {delivery.response_code}</span>
                        )}
                        <span>{delivery.attempts} attempt{delivery.attempts !== 1 ? "s" : ""}</span>
                      </div>
                    </div>
                  ))}
                </div>
                )}
              </CardContent>
            </Card>
          )}
        </div>
      </div>

      {showCreate && (
        <CreateEndpointDialog
          availableEvents={available_events}
          onClose={() => setShowCreate(false)}
          onCreated={() => {
            setShowCreate(false);
            fetchEndpoints();
          }}
        />
      )}

      <ConfirmDialog
        open={!!deleteTarget}
        onOpenChange={(open) => !open && setDeleteTarget(null)}
        title="Delete Webhook Endpoint"
        description="This will permanently delete this endpoint and all its delivery history. This cannot be undone."
        resourceName={deleteTarget?.url}
        resourceType="Endpoint"
        confirmLabel="Delete"
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

function CreateEndpointDialog({
  availableEvents,
  onClose,
  onCreated,
}: {
  availableEvents: string[];
  onClose: () => void;
  onCreated: () => void;
}) {
  const [url, setUrl] = useState("");
  const [events, setEvents] = useState<string[]>([]);
  const [description, setDescription] = useState("");
  const [creating, setCreating] = useState(false);
  const [errors, setErrors] = useState<Record<string, string[]>>({});
  const [createdSecret, setCreatedSecret] = useState<string | null>(null);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setCreating(true);
    setErrors({});

    const res = await fetch("/api/webhooks", {
      method: "POST",
      headers: {
        Accept: "application/json",
        "Content-Type": "application/json",
        "X-XSRF-TOKEN": getCsrfToken(),
      },
      body: JSON.stringify({ url, events, description: description || null }),
    });

    const data = await res.json();
    setCreating(false);

    if (!res.ok) {
      if (res.status === 422 && data.errors) {
        setErrors(data.errors);
      } else {
        toast.error(data.message || "Could not create the endpoint. Please check your input and try again.");
      }
      return;
    }

    setCreatedSecret(data.secret);
    toast.success("Webhook endpoint created.");
  };

  if (createdSecret) {
    return (
      <div className="fixed inset-0 z-50 flex items-center justify-center bg-background/80 backdrop-blur-sm">
        <Card className="w-full max-w-md mx-4 max-h-[90vh] overflow-y-auto">
          <CardHeader>
            <CardTitle>Endpoint Created</CardTitle>
            <CardDescription>
              Save this secret now. It will only be shown once.
            </CardDescription>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="flex items-center gap-2">
              <code className="flex-1 rounded-md bg-muted px-3 py-2 text-sm font-mono break-all">
                {createdSecret}
              </code>
              <Button
                variant="outline"
                size="icon"
                onClick={() => {
                  navigator.clipboard.writeText(createdSecret);
                  toast.success("Secret copied.");
                }}
                aria-label="Copy webhook secret"
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
          <CardTitle>Add Webhook Endpoint</CardTitle>
          <CardDescription>
            We'll send HTTP POST requests to this URL when events occur.
          </CardDescription>
        </CardHeader>
        <CardContent>
          <form onSubmit={handleSubmit} className="space-y-4">
            <div className="space-y-2">
              <Label htmlFor="webhook-url">Endpoint URL</Label>
              <Input
                id="webhook-url"
                type="url"
                placeholder="https://example.com/webhook"
                value={url}
                onChange={(e) => setUrl(e.target.value)}
                required
                aria-describedby={errors.url ? "webhook-url-error" : undefined}
                aria-invalid={!!errors.url}
              />
              {errors.url && (
                <p id="webhook-url-error" className="text-xs text-destructive">{errors.url[0]}</p>
              )}
            </div>

            <div className="space-y-2">
              <Label>Events</Label>
              <div className="space-y-2">
                {availableEvents.map((event) => (
                  <label key={event} className="flex items-center gap-2 text-sm">
                    <input
                      type="checkbox"
                      checked={events.includes(event)}
                      onChange={(e) =>
                        setEvents(
                          e.target.checked
                            ? [...events, event]
                            : events.filter((ev) => ev !== event)
                        )
                      }
                      className="rounded"
                    />
                    {event}
                  </label>
                ))}
              </div>
              {errors.events && (
                <p id="webhook-events-error" className="text-xs text-destructive" role="alert">{errors.events[0]}</p>
              )}
            </div>

            <div className="space-y-2">
              <Label htmlFor="webhook-description">Description (optional)</Label>
              <Input
                id="webhook-description"
                placeholder="Production server"
                value={description}
                onChange={(e) => setDescription(e.target.value)}
              />
            </div>

            <div className="flex gap-2">
              <Button type="button" variant="outline" className="flex-1" onClick={onClose}>
                Cancel
              </Button>
              <LoadingButton type="submit" className="flex-1" loading={creating} loadingText="Creating..." disabled={events.length === 0}>
                Create endpoint
              </LoadingButton>
            </div>
          </form>
        </CardContent>
      </Card>
    </div>
  );
}
