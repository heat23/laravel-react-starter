import { Key } from 'lucide-react';

import { useState } from 'react';

import { Head, Link, router, usePage } from '@inertiajs/react';

import { AdminDataTable } from '@/Components/admin/AdminDataTable';
import PageHeader from '@/Components/layout/PageHeader';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import { ConfirmDialog } from '@/Components/ui/confirm-dialog';
import { Input } from '@/Components/ui/input';
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/Components/ui/table';
import { useAdminFilters } from '@/hooks/useAdminFilters';
import { useNavigationState } from '@/hooks/useNavigationState';
import AdminLayout from '@/Layouts/AdminLayout';
import { formatDate, formatRelativeTime } from '@/lib/format';
import type { PageProps } from '@/types';
import type { AdminTokensIndexProps } from '@/types/admin';

export default function AdminTokensIndex({ tokens, filters }: AdminTokensIndexProps) {
  const isSuperAdmin = usePage<PageProps>().props.auth.user?.is_super_admin ?? false;
  const [revokeId, setRevokeId] = useState<number | null>(null);
  const [revokeTokenName, setRevokeTokenName] = useState<string>('');
  const [isRevoking, setIsRevoking] = useState(false);

  const { search, setSearch, handlePage, clearFilters } = useAdminFilters({
    route: '/admin/tokens/list',
    filters,
  });
  const isNavigating = useNavigationState();

  function handleRevoke(): Promise<void> {
    if (revokeId === null) return Promise.resolve();
    return new Promise((resolve, reject) => {
      setIsRevoking(true);
      router.delete(`/admin/tokens/${revokeId}`, {
        onSuccess: () => {
          setRevokeId(null);
          setIsRevoking(false);
          resolve();
        },
        onError: () => {
          setIsRevoking(false);
          reject();
        },
      });
    });
  }

  return (
    <AdminLayout>
      <Head title="Admin - All Tokens" />
      <PageHeader
        title="All API Tokens"
        subtitle="Browse and revoke user API tokens"
        actions={
          <Button variant="outline" size="sm" asChild>
            <Link href="/admin/tokens">Back to Dashboard</Link>
          </Button>
        }
      />

      <div className="container py-8 space-y-4">
        <div className="flex flex-col sm:flex-row gap-3">
          <Input
            className="max-w-xs"
            placeholder="Search by name, user..."
            value={search}
            onChange={(e) => setSearch(e.target.value)}
            aria-label="Search tokens"
          />
          {filters.search && (
            <Button variant="ghost" size="sm" onClick={clearFilters}>
              Clear search
            </Button>
          )}
        </div>

        <AdminDataTable
          isEmpty={tokens.data.length === 0}
          isNavigating={isNavigating}
          pagination={tokens}
          onPage={handlePage}
          paginationLabel="tokens"
          emptyIcon={Key}
          emptyTitle="No tokens found"
          emptyDescription={
            filters.search
              ? 'No tokens match your search.'
              : 'No API tokens have been created yet.'
          }
          emptyAction={
            filters.search ? (
              <Button variant="outline" size="sm" onClick={clearFilters}>
                Clear search
              </Button>
            ) : undefined
          }
        >
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Token Name</TableHead>
                <TableHead>User</TableHead>
                <TableHead>Abilities</TableHead>
                <TableHead>Last Used</TableHead>
                <TableHead>Expires</TableHead>
                <TableHead>Created</TableHead>
                {isSuperAdmin && <TableHead className="text-right">Actions</TableHead>}
              </TableRow>
            </TableHeader>
            <TableBody>
              {tokens.data.map((token) => (
                <TableRow key={token.id}>
                  <TableCell className="text-sm font-medium">{token.token_name}</TableCell>
                  <TableCell>
                    <div className="text-sm font-medium">
                      <Link
                        href={`/admin/users/${token.user_id}`}
                        className="hover:underline"
                      >
                        {token.user_name}
                      </Link>
                    </div>
                    <div className="text-xs text-muted-foreground">{token.user_email}</div>
                  </TableCell>
                  <TableCell>
                    <div className="flex flex-wrap gap-1">
                      {(token.abilities ?? []).slice(0, 3).map((ability) => (
                        <Badge key={ability} variant="outline" className="text-xs">
                          {ability}
                        </Badge>
                      ))}
                      {(token.abilities ?? []).length > 3 && (
                        <Badge variant="outline" className="text-xs">
                          +{(token.abilities ?? []).length - 3}
                        </Badge>
                      )}
                      {(token.abilities ?? []).length === 0 && (
                        <span className="text-xs text-muted-foreground">—</span>
                      )}
                    </div>
                  </TableCell>
                  <TableCell className="text-sm text-muted-foreground">
                    {token.last_used_at ? formatRelativeTime(token.last_used_at) : 'Never'}
                  </TableCell>
                  <TableCell className="text-sm text-muted-foreground">
                    {token.expires_at ? formatDate(token.expires_at) : '—'}
                  </TableCell>
                  <TableCell className="text-sm text-muted-foreground">
                    {formatDate(token.created_at)}
                  </TableCell>
                  {isSuperAdmin && (
                    <TableCell className="text-right">
                      <Button
                        variant="destructive"
                        size="sm"
                        disabled={isRevoking && revokeId === token.id}
                        onClick={() => {
                          setRevokeId(token.id);
                          setRevokeTokenName(token.token_name);
                        }}
                      >
                        Revoke
                      </Button>
                    </TableCell>
                  )}
                </TableRow>
              ))}
            </TableBody>
          </Table>
        </AdminDataTable>
      </div>

      <ConfirmDialog
        open={revokeId !== null}
        onOpenChange={(open) => !open && setRevokeId(null)}
        onConfirm={handleRevoke}
        title="Revoke API Token"
        description="This will permanently delete the token. The user will need to create a new token."
        resourceName={revokeTokenName}
        resourceType="Token"
        variant="destructive"
        confirmLabel="Revoke"
        loadingLabel="Revoking..."
      />
    </AdminLayout>
  );
}
