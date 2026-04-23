import { Database } from 'lucide-react';

import { useState } from 'react';

import { Head, router, usePage } from '@inertiajs/react';

import PageHeader from '@/Components/layout/PageHeader';
import { Badge } from '@/Components/ui/badge';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card';
import { ConfirmDialog } from '@/Components/ui/confirm-dialog';
import { LoadingButton } from '@/Components/ui/loading-button';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/Components/ui/select';
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/Components/ui/table';
import AdminLayout from '@/Layouts/AdminLayout';
import type { PageProps } from '@/types';
import type { AdminCacheIndexProps } from '@/types/admin';

export default function AdminCacheIndex({ cacheKeys, scopes }: AdminCacheIndexProps) {
  const isSuperAdmin = usePage<PageProps>().props.auth.user?.is_super_admin ?? false;
  const [selectedScope, setSelectedScope] = useState<string>('all');
  const [confirmOpen, setConfirmOpen] = useState(false);
  const [isFlushing, setIsFlushing] = useState(false);

  function handleFlush(): Promise<void> {
    return new Promise((resolve, reject) => {
      setIsFlushing(true);
      router.post(
        '/admin/cache/flush',
        { scope: selectedScope },
        {
          onSuccess: () => {
            setConfirmOpen(false);
            setIsFlushing(false);
            resolve();
          },
          onError: () => {
            setIsFlushing(false);
            reject();
          },
        }
      );
    });
  }

  return (
    <AdminLayout>
      <Head title="Admin - Cache Management" />
      <PageHeader
        title="Cache Management"
        subtitle="View cached admin data and flush cache scopes"
      />

      <div className="container py-8 space-y-6">
        <Card>
          <CardHeader>
            <CardTitle>Cache Keys</CardTitle>
            <CardDescription>Current status of all admin cache entries.</CardDescription>
          </CardHeader>
          <CardContent>
            <div className="overflow-x-auto">
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>Key Name</TableHead>
                    <TableHead>Cache Key</TableHead>
                    <TableHead>Status</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {cacheKeys.map((item) => (
                    <TableRow key={item.key}>
                      <TableCell className="font-medium">{item.name}</TableCell>
                      <TableCell>
                        <code className="text-xs font-mono text-muted-foreground">{item.key}</code>
                      </TableCell>
                      <TableCell>
                        {item.exists ? (
                          <Badge variant="success">Cached</Badge>
                        ) : (
                          <Badge variant="secondary">Empty</Badge>
                        )}
                      </TableCell>
                    </TableRow>
                  ))}
                </TableBody>
              </Table>
            </div>
          </CardContent>
        </Card>

        {isSuperAdmin && (
          <Card>
            <CardHeader>
              <CardTitle>Flush Cache</CardTitle>
              <CardDescription>
                Manually invalidate a cache scope. Use this after data corrections or to force a stat refresh.
              </CardDescription>
            </CardHeader>
            <CardContent>
              <div className="flex items-center gap-3">
                <Select value={selectedScope} onValueChange={setSelectedScope}>
                  <SelectTrigger className="w-[200px]" aria-label="Select cache scope">
                    <SelectValue placeholder="Select scope" />
                  </SelectTrigger>
                  <SelectContent>
                    {scopes.map((scope) => (
                      <SelectItem key={scope} value={scope}>
                        {scope === 'all' ? 'All Caches' : scope.replace('_', ' ').replace(/\b\w/g, (c) => c.toUpperCase())}
                      </SelectItem>
                    ))}
                  </SelectContent>
                </Select>
                <LoadingButton
                  variant="destructive"
                  loading={isFlushing}
                  onClick={() => setConfirmOpen(true)}
                >
                  <Database className="mr-2 h-4 w-4" />
                  Flush
                </LoadingButton>
              </div>
            </CardContent>
          </Card>
        )}
      </div>

      <ConfirmDialog
        open={confirmOpen}
        onOpenChange={setConfirmOpen}
        onConfirm={handleFlush}
        title="Flush Cache"
        description={`Are you sure you want to flush the "${selectedScope}" cache scope? This will force the next request to rebuild these values from the database.`}
        variant="destructive"
        confirmLabel="Flush Cache"
      />
    </AdminLayout>
  );
}
