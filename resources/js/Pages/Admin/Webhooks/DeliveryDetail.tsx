import { ArrowLeft, Clock, Hash, Link2, RefreshCw, Zap } from 'lucide-react';

import { Head, Link } from '@inertiajs/react';

import { Badge } from '@/Components/ui/badge';
import {
  Breadcrumb,
  BreadcrumbItem,
  BreadcrumbLink,
  BreadcrumbList,
  BreadcrumbPage,
  BreadcrumbSeparator,
} from '@/Components/ui/breadcrumb';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import AdminLayout from '@/Layouts/AdminLayout';
import { formatDate, formatRelativeTime } from '@/lib/format';
import type { AdminWebhookDeliveryDetailProps } from '@/types/admin';

function statusVariant(
  status: string,
): 'default' | 'destructive' | 'secondary' | 'outline' {
  if (status === 'success') return 'default';
  if (status === 'failed') return 'destructive';
  return 'secondary';
}

function responseCodeVariant(
  code: number | null,
): 'default' | 'destructive' | 'secondary' | 'outline' {
  if (code === null) return 'secondary';
  if (code >= 200 && code < 300) return 'default';
  return 'destructive';
}

export default function WebhookDeliveryDetail({
  delivery,
}: AdminWebhookDeliveryDetailProps) {
  return (
    <AdminLayout>
      <Head title={`Admin - Delivery #${delivery.id}`} />

      <div className="container py-6 space-y-6">
        {/* Breadcrumb */}
        <Breadcrumb>
          <BreadcrumbList>
            <BreadcrumbItem>
              <BreadcrumbLink asChild>
                <Link href="/admin">Admin</Link>
              </BreadcrumbLink>
            </BreadcrumbItem>
            <BreadcrumbSeparator />
            <BreadcrumbItem>
              <BreadcrumbLink asChild>
                <Link href="/admin/webhooks">Webhooks</Link>
              </BreadcrumbLink>
            </BreadcrumbItem>
            <BreadcrumbSeparator />
            <BreadcrumbItem>
              <BreadcrumbPage>Delivery #{delivery.id}</BreadcrumbPage>
            </BreadcrumbItem>
          </BreadcrumbList>
        </Breadcrumb>

        {/* Summary card */}
        <Card>
          <CardHeader>
            <CardTitle className="flex flex-wrap items-center gap-3">
              <Badge variant={statusVariant(delivery.status)} className="capitalize">
                {delivery.status}
              </Badge>
              <Badge variant="secondary">{delivery.event_type}</Badge>
              <span className="text-muted-foreground text-sm font-normal">
                #{delivery.id}
              </span>
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
              <div>
                <p className="text-xs text-muted-foreground mb-1 flex items-center gap-1">
                  <Link2 className="h-3 w-3" aria-hidden="true" />
                  Endpoint
                </p>
                <p className="text-sm font-mono break-all">
                  {delivery.endpoint_id ? (
                    <Link
                      href={`/admin/webhooks/endpoints`}
                      className="hover:underline"
                    >
                      {delivery.endpoint_url}
                    </Link>
                  ) : (
                    <span className="text-muted-foreground">
                      {delivery.endpoint_url}
                    </span>
                  )}
                  {delivery.endpoint_deleted && (
                    <Badge variant="destructive" className="ml-2 text-xs">
                      Deleted
                    </Badge>
                  )}
                </p>
              </div>

              <div>
                <p className="text-xs text-muted-foreground mb-1">Owner</p>
                <p className="text-sm">
                  {delivery.user_id ? (
                    <Link
                      href={`/admin/users/${delivery.user_id}`}
                      className="hover:underline"
                    >
                      {delivery.user_name}
                    </Link>
                  ) : (
                    <span className="text-muted-foreground">
                      {delivery.user_name}
                    </span>
                  )}
                  {delivery.user_email && (
                    <span className="block text-xs text-muted-foreground">
                      {delivery.user_email}
                    </span>
                  )}
                </p>
              </div>

              <div>
                <p className="text-xs text-muted-foreground mb-1 flex items-center gap-1">
                  <Hash className="h-3 w-3" aria-hidden="true" />
                  Response Code
                </p>
                <Badge variant={responseCodeVariant(delivery.response_code)}>
                  {delivery.response_code ?? 'timeout / no response'}
                </Badge>
              </div>

              <div>
                <p className="text-xs text-muted-foreground mb-1 flex items-center gap-1">
                  <RefreshCw className="h-3 w-3" aria-hidden="true" />
                  Attempts
                </p>
                <p className="text-sm">{delivery.attempts}</p>
              </div>

              <div>
                <p className="text-xs text-muted-foreground mb-1 flex items-center gap-1">
                  <Clock className="h-3 w-3" aria-hidden="true" />
                  Created
                </p>
                <p className="text-sm" title={formatDate(delivery.created_at)}>
                  {formatRelativeTime(delivery.created_at)}
                </p>
              </div>

              {delivery.delivered_at && (
                <div>
                  <p className="text-xs text-muted-foreground mb-1 flex items-center gap-1">
                    <Zap className="h-3 w-3" aria-hidden="true" />
                    Delivered At
                  </p>
                  <p className="text-sm" title={formatDate(delivery.delivered_at)}>
                    {formatRelativeTime(delivery.delivered_at)}
                  </p>
                </div>
              )}

              <div className="sm:col-span-2 lg:col-span-3">
                <p className="text-xs text-muted-foreground mb-1">UUID</p>
                <p className="text-sm font-mono text-muted-foreground">
                  {delivery.uuid}
                </p>
              </div>
            </div>
          </CardContent>
        </Card>

        {/* Request payload */}
        <Card>
          <CardHeader>
            <CardTitle className="text-base">Request Payload</CardTitle>
          </CardHeader>
          <CardContent>
            {delivery.payload ? (
              <pre className="bg-muted p-4 rounded-lg overflow-auto text-sm max-h-[400px]">
                {JSON.stringify(delivery.payload, null, 2)}
              </pre>
            ) : (
              <p className="text-sm text-muted-foreground">No payload recorded.</p>
            )}
          </CardContent>
        </Card>

        {/* Response body */}
        <Card>
          <CardHeader>
            <CardTitle className="text-base">Response Body</CardTitle>
          </CardHeader>
          <CardContent>
            {delivery.response_body ? (
              <pre className="bg-muted p-4 rounded-lg overflow-auto text-sm max-h-[400px] whitespace-pre-wrap break-all">
                {delivery.response_body}
              </pre>
            ) : (
              <p className="text-sm text-muted-foreground">
                No response body recorded.
              </p>
            )}
          </CardContent>
        </Card>

        <Button variant="outline" asChild>
          <Link href="/admin/webhooks">
            <ArrowLeft className="mr-2 h-4 w-4" />
            Back to Webhooks
          </Link>
        </Button>
      </div>
    </AdminLayout>
  );
}
