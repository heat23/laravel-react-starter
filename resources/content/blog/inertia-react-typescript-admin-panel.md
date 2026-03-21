---
title: Building a Type-Safe Admin Panel with Inertia.js, React, and TypeScript
slug: inertia-react-typescript-admin-panel
description: Why we chose Inertia.js + React over Filament for the admin panel, and the TypeScript patterns that make it maintainable.
date: 2026-02-28
readingTime: 10 min read
tags: [Laravel, React, TypeScript, Inertia.js, admin]
---

## Why Not Filament?

Filament is an excellent admin panel for Laravel. If you're building in Livewire or Blade, it's hard to beat. But if your main application is already React + TypeScript, Filament means maintaining two frontend stacks: React for the user-facing app and PHP/Livewire for the admin.

The practical problems:

- Your React component library doesn't work in Filament — you maintain two design systems
- TypeScript catches type errors in your app code but not in Filament's PHP-based resource definitions
- Junior developers have to context-switch between paradigms in the same codebase
- Search, filtering, and bulk operations in Filament follow different patterns than your React tables

The alternative: build the admin in React + TypeScript using the same Inertia.js patterns as the rest of the app.

## The Inertia Admin Architecture

The admin panel lives at `/admin/*` routes and uses a shared `AdminLayout` component:

```tsx
// resources/js/Layouts/AdminLayout.tsx
export default function AdminLayout({ children }: { children: React.ReactNode }) {
  const { auth, features } = usePage<PageProps>().props;

  return (
    <div className="min-h-screen bg-background">
      <AdminSidebar features={features} />
      <main className="lg:pl-64">
        <AdminHeader user={auth.user} />
        {children}
      </main>
    </div>
  );
}
```

Each admin page is a standard Inertia page component that extends `AdminLayout`. The controller passes typed props:

```php
// app/Http/Controllers/Admin/UserController.php
public function index(Request $request): Response
{
    $users = User::with(['subscriptions', 'auditLogs' => fn ($q) => $q->latest()->limit(1)])
        ->when($request->search, fn ($q) => $q->where('name', 'like', "%{$request->search}%"))
        ->paginate(config('pagination.admin'));

    return Inertia::render('Admin/Users/Index', [
        'users' => UserResource::collection($users),
        'filters' => $request->only('search', 'status', 'plan'),
    ]);
}
```

## TypeScript-First Props

Define your page props as TypeScript interfaces that mirror the PHP data:

```typescript
// resources/js/types/admin.ts
export interface AdminUserRow {
  id: number;
  name: string;
  email: string;
  created_at: string;
  is_admin: boolean;
  subscription_status: 'active' | 'canceled' | 'trialing' | null;
  plan_name: string | null;
  last_active_at: string | null;
}

export interface AdminUsersPageProps extends PageProps {
  users: PaginatedResource<AdminUserRow>;
  filters: {
    search?: string;
    status?: string;
    plan?: string;
  };
}
```

The TypeScript interface acts as documentation for the controller contract. If the PHP controller adds or renames a field, TypeScript catches the mismatch at build time.

## The Data Table Pattern

Admin tables in React follow a consistent pattern using URL-based filters:

```tsx
export default function UsersIndex() {
  const { users, filters } = usePage<AdminUsersPageProps>().props;
  const { params, setParam, clearFilters } = useAdminFilters(filters);

  return (
    <AdminLayout>
      <div className="space-y-4">
        <AdminFilters
          value={params.search ?? ''}
          onSearch={(value) => setParam('search', value)}
          onClear={clearFilters}
        />
        <DataTable
          columns={userColumns}
          data={users.data}
          pagination={users.meta}
        />
      </div>
    </AdminLayout>
  );
}
```

Filters live in URL params — search, sort, page. This means the state is bookmarkable and survives a browser refresh. `useAdminFilters` wraps `router.get()` with `preserveState: true` so filter changes don't reset pagination.

## Bulk Operations

The `useAdminAction` hook handles async admin mutations with loading state and confirmation dialogs:

```tsx
const { execute, loading } = useAdminAction({
  action: (ids: number[]) =>
    router.post(route('admin.users.bulk-deactivate'), { ids }),
  confirm: {
    title: 'Deactivate users',
    description: `Deactivate ${selectedIds.length} users?`,
  },
  onSuccess: () => setSelectedIds([]),
});
```

The hook handles the confirmation dialog, loading state during the request, and toast notification on completion.

## Why This Beats Filament for React Teams

- **One design system** — Radix UI + Tailwind components work in both the app and admin
- **Type safety end to end** — PHP controller → Inertia props → TypeScript interface → React component
- **Familiar patterns** — React developers extend the admin using the same `usePage`, `router`, hooks they already know
- **Co-located tests** — Vitest tests for admin React components live next to the components in `resources/js/Pages/Admin/`

The trade-off: you build more yourself. Filament gives you a working CRUD panel faster. This approach gives you a panel that fits your stack and is maintainable by your team.

Laravel React Starter ships this admin pattern out of the box: user management, subscription oversight, audit logs, feature flag toggles, health checks, and config viewer.
