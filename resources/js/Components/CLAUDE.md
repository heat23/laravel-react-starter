# Components

## Structure
- `ui/` - Radix primitives, use CVA for variants
- `layout/` - App shell, headers (PageHeader, AppLayout)
- `branding/` - Logo (customize via CSS vars)
- `theme/` - ThemeProvider, toggle, context

## Patterns
- Use `cn()` from `@/lib/utils` for class merging
- Dark mode: semantic tokens (bg-background, text-foreground)
- Forms: React Hook Form + Zod
- Icons: Lucide React only

## Adding Components
Follow shadcn/ui patterns. Import Radix, wrap with CVA variants.
