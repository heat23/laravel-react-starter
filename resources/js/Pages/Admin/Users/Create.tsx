import { Head, Link, useForm } from '@inertiajs/react';

import PageHeader from '@/Components/layout/PageHeader';
import {
  Breadcrumb,
  BreadcrumbItem,
  BreadcrumbLink,
  BreadcrumbList,
  BreadcrumbPage,
  BreadcrumbSeparator,
} from '@/Components/ui/breadcrumb';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Checkbox } from '@/Components/ui/checkbox';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { LoadingButton } from '@/Components/ui/loading-button';
import AdminLayout from '@/Layouts/AdminLayout';

export default function AdminCreateUser() {
  const form = useForm({
    name: '',
    email: '',
    password: '',
    is_admin: false,
  });

  function submit(e: React.FormEvent) {
    e.preventDefault();
    form.post('/admin/users');
  }

  return (
    <AdminLayout>
      <Head title="Admin - Create User" />

      <div className="container py-6 space-y-6">
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
                <Link href="/admin/users">Users</Link>
              </BreadcrumbLink>
            </BreadcrumbItem>
            <BreadcrumbSeparator />
            <BreadcrumbItem>
              <BreadcrumbPage>Create User</BreadcrumbPage>
            </BreadcrumbItem>
          </BreadcrumbList>
        </Breadcrumb>

        <PageHeader title="Create User" subtitle="Add a new user account" />

        <Card className="max-w-lg">
          <CardHeader>
            <CardTitle>User Details</CardTitle>
          </CardHeader>
          <CardContent>
            <form onSubmit={submit} className="space-y-4">
              <div className="space-y-1">
                <Label htmlFor="name">Name</Label>
                <Input
                  id="name"
                  value={form.data.name}
                  onChange={(e) => form.setData('name', e.target.value)}
                  aria-describedby={form.errors.name ? 'name-error' : undefined}
                />
                {form.errors.name && (
                  <p id="name-error" className="text-sm text-destructive">
                    {form.errors.name}
                  </p>
                )}
              </div>

              <div className="space-y-1">
                <Label htmlFor="email">Email</Label>
                <Input
                  id="email"
                  type="email"
                  value={form.data.email}
                  onChange={(e) => form.setData('email', e.target.value)}
                  aria-describedby={
                    form.errors.email ? 'email-error' : undefined
                  }
                />
                {form.errors.email && (
                  <p id="email-error" className="text-sm text-destructive">
                    {form.errors.email}
                  </p>
                )}
              </div>

              <div className="space-y-1">
                <Label htmlFor="password">Password</Label>
                <Input
                  id="password"
                  type="password"
                  value={form.data.password}
                  onChange={(e) => form.setData('password', e.target.value)}
                  aria-describedby={
                    form.errors.password ? 'password-error' : undefined
                  }
                />
                {form.errors.password && (
                  <p id="password-error" className="text-sm text-destructive">
                    {form.errors.password}
                  </p>
                )}
              </div>

              <div className="flex items-center gap-2">
                <Checkbox
                  id="is_admin"
                  checked={form.data.is_admin}
                  onCheckedChange={(checked) =>
                    form.setData('is_admin', checked === true)
                  }
                />
                <Label htmlFor="is_admin">Grant admin access</Label>
              </div>

              <div className="flex gap-3 pt-2">
                <LoadingButton type="submit" loading={form.processing}>
                  Create User
                </LoadingButton>
                <Link href="/admin/users">
                  <button
                    type="button"
                    className="inline-flex items-center justify-center rounded-md border border-input bg-background px-4 py-2 text-sm font-medium shadow-sm hover:bg-accent hover:text-accent-foreground"
                  >
                    Cancel
                  </button>
                </Link>
              </div>
            </form>
          </CardContent>
        </Card>
      </div>
    </AdminLayout>
  );
}
