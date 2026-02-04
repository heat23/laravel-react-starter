# Pages

## Structure
- `Auth/` - Login, Register, password flows
- `Profile/` - User settings, password update
- `Dashboard.tsx` - Main authenticated landing
- `Welcome.tsx` - Public landing page

## Patterns
- Use Inertia's `usePage()` for shared props
- Forms via `useForm()` from @inertiajs/react
- Layouts: AuthLayout (public), DashboardLayout (auth)

## Props
Backend passes via `Inertia::render()`:
```tsx
const { auth, flash, features } = usePage<PageProps>().props;
```

## Feature-Gated UI
Auth pages receive `features` prop for conditional rendering:
```tsx
{features?.socialAuth && <SocialButtons />}
```

## Navigation
Use `<Link href={route('name')}>` for SPA navigation.
